<?php
/**
 * check-access.php  —  role-aware download gate
 */

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

$size = filesize($abs);
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
    if ($fp) {
        $chunks = 0;
        $total = 0;
        while (!feof($fp)) {
            $data = fread($fp, CHUNK_SIZE);
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
    }
    exit;
}
readfile($abs);
exit;
