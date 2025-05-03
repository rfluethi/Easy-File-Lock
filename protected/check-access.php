<?php
/**
 * check-access.php  —  role-aware download gate
 * Zugriffskontrolle für geschützte Dateien
 * Diese Datei muss im WebRoot-Verzeichnis (z.B. protected/) liegen.
 */

// WordPress-Konfiguration
define('WP_USE_THEMES', false);

// ZUERST die Konfiguration laden!
$config_file = dirname(dirname(__DIR__)) . '/secure-files/config/secure-config.php';
if (!file_exists($config_file)) {
    die('Fehler: Konfigurationsdatei nicht gefunden. Bitte Installation überprüfen.');
}
require_once $config_file;

// Jetzt stehen alle Konstanten zur Verfügung
$memory_limit = ini_get('memory_limit');
if (intval($memory_limit) < intval(MIN_MEMORY_LIMIT)) {
    die('PHP Memory-Limit zu niedrig. Mindestens ' . MIN_MEMORY_LIMIT . ' erforderlich.');
}

// Jetzt erst WordPress laden
if (!file_exists(WP_CORE_PATH)) {
    die('Fehler: WordPress nicht gefunden. Bitte Pfad in secure-config.php überprüfen.');
}
require_once WP_CORE_PATH;

// Debug-Modus
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// vault root (fallback if constant missing)
if (!defined('SECURE_FILE_PATH')) {
    define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
}

// Log-Verzeichnis prüfen
if (!is_dir(LOG_DIR)) {
    if (!mkdir(LOG_DIR, 0755, true)) {
        die('Log-Verzeichnis konnte nicht erstellt werden');
    }
}
if (!file_exists(LOG_FILE)) {
    if (!touch(LOG_FILE)) {
        die('Log-Datei konnte nicht erstellt werden');
    }
    chmod(LOG_FILE, 0644);
}

// Log-Rotation
if (file_exists(LOG_FILE) && filesize(LOG_FILE) > LOG_MAX_SIZE) {
    $old_logs = glob(LOG_FILE . '.*');
    if (count($old_logs) >= LOG_MAX_FILES) {
        usort($old_logs, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        unlink($old_logs[0]);
    }
    rename(LOG_FILE, LOG_FILE . '.' . date('Y-m-d-H-i-s'));
}

// Debug: Aktuelle Rollen loggen
$current = wp_get_current_user();
if (DEBUG_MODE) {
    file_put_contents(LOG_FILE, 'Aktuelle Rollen: ' . print_r($current->roles, true) . PHP_EOL, FILE_APPEND);
}

// only logged-in users
if (!is_user_logged_in()) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, 'Benutzer nicht eingeloggt' . PHP_EOL, FILE_APPEND);
    }
    auth_redirect();
    exit;
}

// requested file (clean)
$rel = $_GET['file'] ?? '';
$rel = ltrim(str_replace(['..', './', '\\'], '', $rel), '/');
if ($rel === '' || substr($rel, -1) === '/') {
    $rel .= 'index.html';
}

// Dateiname validieren
if (!preg_match('/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/', $rel)) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Ungültiger Dateiname: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(400);
    exit('Invalid filename');
}

// role + folder check
$roles = $current->roles;
$allowed = false;
foreach ($roles as $role) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Prüfe Rolle: $role" . PHP_EOL, FILE_APPEND);
    }
    if ($role === 'administrator') {
        $allowed = true;
        break;
    }
    if (isset($role_folders[$role])) {
        if (str_starts_with($rel, $role_folders[$role] . '/')) {
            $allowed = true;
            break;
        }
    }
}
if (!$allowed) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Zugriff verweigert für: $rel" . PHP_EOL, FILE_APPEND);
        file_put_contents(LOG_FILE, "Benutzer-Rollen: " . implode(', ', $roles) . PHP_EOL, FILE_APPEND);
    }
    status_header(403);
    exit('Forbidden');
}

// path sanity
$abs = realpath(SECURE_FILE_PATH . '/' . $rel);
if (
    $abs === false ||
    !is_file($abs) ||
    strncmp($abs, SECURE_FILE_PATH, strlen(SECURE_FILE_PATH)) !== 0
) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Datei nicht gefunden: $rel" . PHP_EOL, FILE_APPEND);
        file_put_contents(LOG_FILE, "Absoluter Pfad: $abs" . PHP_EOL, FILE_APPEND);
    }
    status_header(404);
    exit('not found');
}

// Dateigröße prüfen
$size = filesize($abs);
if ($size === false) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Fehler beim Lesen der Dateigröße: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(500);
    exit('Internal server error');
}
if ($size > MAX_FILE_SIZE) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Datei zu groß: $rel ($size Bytes)" . PHP_EOL, FILE_APPEND);
    }
    status_header(413);
    exit('File too large');
}

// MIME + headers
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$mime = $allowed_mime_types[$ext] ?? 'application/octet-stream';

// Basis-Header
header("Content-Type: $mime");
header('Cache-Control: ' . CACHE_CONTROL);

// Sicherheits-Header
foreach (SECURITY_HEADERS as $header) {
    header($header);
}

header("Content-Length: $size");

// Debug-Logging
if (DEBUG_MODE) {
    file_put_contents(LOG_FILE, sprintf(
        "Datei-Transfer: %s (%s, %d Bytes)" . PHP_EOL,
        $rel,
        $mime,
        $size
    ), FILE_APPEND);
}

// send file (chunked for large payloads)
if ($size > MAX_DIRECT_DOWNLOAD_SIZE) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Starte Chunked Download" . PHP_EOL, FILE_APPEND);
    }
    @set_time_limit(0);
    while (ob_get_level())
        ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp === false) {
        if (DEBUG_MODE) {
            file_put_contents(LOG_FILE, "Fehler beim Öffnen der Datei: $rel" . PHP_EOL, FILE_APPEND);
        }
        status_header(500);
        exit('Internal server error');
    }
    $chunks = 0;
    $total = 0;
    while (!feof($fp)) {
        $data = fread($fp, CHUNK_SIZE);
        if ($data === false) {
            if (DEBUG_MODE) {
                file_put_contents(LOG_FILE, "Fehler beim Lesen der Datei: $rel" . PHP_EOL, FILE_APPEND);
            }
            fclose($fp);
            status_header(500);
            exit('Internal server error');
        }
        echo $data;
        flush();
        $chunks++;
        $total += strlen($data);
        if (DEBUG_MODE && $chunks % 10 === 0) {
            file_put_contents(LOG_FILE, sprintf(
                "Chunk %d: %d Bytes übertragen" . PHP_EOL,
                $chunks,
                $total
            ), FILE_APPEND);
        }
    }
    fclose($fp);
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, sprintf(
            "Download abgeschlossen: %d Chunks, %d Bytes" . PHP_EOL,
            $chunks,
            $total
        ), FILE_APPEND);
    }
    exit;
}

// Direkter Download für kleine Dateien
$result = readfile($abs);
if ($result === false) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Fehler beim Lesen der Datei: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(500);
    exit('Internal server error');
}
exit;

