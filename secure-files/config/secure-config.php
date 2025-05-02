<?php
/**
 * Konfigurationsdatei für den Dateitresor
 * 
 * Diese Datei enthält alle konfigurierbaren Einstellungen für den Dateitresor.
 * Änderungen an der Funktionalität sollten hier vorgenommen werden.
 */

// Rollen und ihre zugehörigen Ordner
$role_folders = [
    'seminar-website-basis' => 's-wsb',
    'cv-interessent'        => 'secure-docs'
];

// Download-Einstellungen
define('MAX_DIRECT_DOWNLOAD_SIZE', 524288);  // 512 KB
define('CHUNK_SIZE', 1048576);              // 1 MB

// Erlaubte Dateiendungen und ihre MIME-Types
$allowed_mime_types = [
    'html' => 'text/html',
    'pdf'  => 'application/pdf',
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp'
];

// Cache-Einstellungen
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false); 