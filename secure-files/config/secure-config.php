<?php
/**
 * Konfigurationsdatei für den Dateitresor
 * 
 * Diese Datei enthält alle konfigurierbaren Einstellungen für den Dateitresor.
 * Änderungen an der Funktionalität sollten hier vorgenommen werden.
 */

// WordPress-Pfad (flexibel)
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/wp-load.php');  // Wenn WordPress in /wordpress/ liegt
// Alternative: define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wp-load.php');  // Wenn WordPress direkt im WebRoot liegt

// Logging-Konfiguration
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('LOG_MAX_FILES', 5);               // Max. 5 Log-Dateien

// Rollenzuordnungen
$role_folders = [
    'subscriber' => 'group-1',
    'contributor' => 'group-2'
];

// Download-Einstellungen
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimale PHP Memory-Limit

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
    'webp' => 'image/webp',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'zip'  => 'application/zip',
    'rar'  => 'application/x-rar-compressed',
    '7z'   => 'application/x-7z-compressed'
];

// Cache-Einstellungen
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Sicherheits-Header
define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\'',
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'  // HTTPS erzwingen
]);

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false); 