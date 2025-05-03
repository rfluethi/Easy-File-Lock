<?php
/**
 * check-access.php  —  role-aware download gate
 */

// PHP-Version prüfen
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4 oder höher erforderlich');
}

// Memory-Limit prüfen
$memory_limit = ini_get('memory_limit');
if (intval($memory_limit) < intval(MIN_MEMORY_LIMIT)) {
    die('PHP Memory-Limit zu niedrig. Mindestens ' . MIN_MEMORY_LIMIT . ' erforderlich.');
}

// WordPress laden
define('WP_USE_THEMES', false);         // skip WP theme bootstrap
require_once WP_CORE_PATH;               // load WordPress core

// Konfiguration laden
require_once SECURE_FILE_PATH . '/config/secure-config.php';

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

// role → folder map (loaded from secure-config.php)
$role_folders = require SECURE_FILE_PATH . '/config/secure-config.php';

// only logged-in users
if (!is_user_logged_in()) {
    if (DEBUG_MODE) {
        error_log('Benutzer nicht eingeloggt');
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
        error_log("Ungültiger Dateiname: $rel");
    }
    status_header(400);
    exit('Invalid filename');
}

// role + folder check
$current = wp_get_current_user();
$roles = $current->roles;
$allowed = false;
foreach ($roles as $role) {
    if (DEBUG_MODE) {
        error_log("Prüfe Rolle: $role");
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
        error_log("Zugriff verweigert für: $rel");
        error_log("Benutzer-Rollen: " . implode(', ', $roles));
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
        error_log("Datei nicht gefunden: $rel");
        error_log("Absoluter Pfad: $abs");
    }
    status_header(404);
    exit('not found');
}

// Dateigröße prüfen
$size = filesize($abs);
if ($size === false) {
    if (DEBUG_MODE) {
        error_log("Fehler beim Lesen der Dateigröße: $rel");
    }
    status_header(500);
    exit('Internal server error');
}
if ($size > MAX_FILE_SIZE) {
    if (DEBUG_MODE) {
        error_log("Datei zu groß: $rel ($size Bytes)");
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
    error_log(sprintf(
        "Datei-Transfer: %s (%s, %d Bytes)",
        $rel,
        $mime,
        $size
    ));
}

// send file (chunked for large payloads)
if ($size > MAX_DIRECT_DOWNLOAD_SIZE) {
    if (DEBUG_MODE) {
        error_log("Starte Chunked Download");
    }
    @set_time_limit(0);
    while (ob_get_level())
        ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp === false) {
        if (DEBUG_MODE) {
            error_log("Fehler beim Öffnen der Datei: $rel");
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
                error_log("Fehler beim Lesen der Datei: $rel");
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
            error_log(sprintf(
                "Chunk %d: %d Bytes übertragen",
                $chunks,
                $total
            ));
        }
    }
    fclose($fp);
    if (DEBUG_MODE) {
        error_log(sprintf(
            "Download abgeschlossen: %d Chunks, %d Bytes",
            $chunks,
            $total
        ));
    }
    exit;
}

// Direkter Download für kleine Dateien
$result = readfile($abs);
if ($result === false) {
    if (DEBUG_MODE) {
        error_log("Fehler beim Lesen der Datei: $rel");
    }
    status_header(500);
    exit('Internal server error');
}
exit;

