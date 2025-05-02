<?php
/**
 * Wachhund für den Dateitresor
 * 
 * Prüft Zugriffsrechte und liefert geschützte Dateien aus.
 */

// WordPress laden
define('WP_USE_THEMES', false);
require_once dirname(__DIR__) . '/main/wp-load.php';

// Konfiguration laden
require_once SECURE_FILE_PATH . '/config/secure-config.php';

// Nur eingeloggte Besucher
if (!is_user_logged_in()) { 
    auth_redirect(); 
    exit; 
}

// Dateipfad aus URL holen & säubern
$rel = $_GET['file'] ?? '';
$rel = ltrim(str_replace(['..','./','\'], '', $rel), '/');
if ($rel === '' || substr($rel, -1) === '/') { 
    $rel .= 'index.html'; 
}

// Ordner- und Rollen-Check
$current = wp_get_current_user();
$roles   = $current->roles;
$allowed = false;

foreach ($roles as $role) {
    if ($role === 'administrator') { 
        $allowed = true; 
        break; 
    }
    if (isset($role_folders[$role])) {
        $prefix = $role_folders[$role] . '/';
        if (str_starts_with($rel, $prefix)) { 
            $allowed = true; 
            break; 
        }
    }
}

if (!$allowed) { 
    status_header(403); 
    exit('Forbidden'); 
}

// Pfad- und Existenz-Prüfung
$abs = realpath(SECURE_FILE_PATH . '/' . $rel);
if ($abs === false || !is_file($abs) || strncmp($abs, SECURE_FILE_PATH, strlen(SECURE_FILE_PATH)) !== 0) {
    status_header(404); 
    exit('not found');
}

// MIME-Type & Header
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$mime = $allowed_mime_types[$ext] ?? 'application/octet-stream';

header("Content-Type: $mime");
header('X-Content-Type-Options: nosniff');
header(CACHE_CONTROL);

$size = filesize($abs);
header("Content-Length: $size");

// Datei ausliefern
if ($size > MAX_DIRECT_DOWNLOAD_SIZE) {
    @set_time_limit(0);
    while (ob_get_level()) ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp) {
        while (!feof($fp)) { 
            echo fread($fp, CHUNK_SIZE); 
            flush(); 
        }
        fclose($fp);
    }
    exit;
}

readfile($abs);
exit; 