<?php
/**
 * Konfigurationsdatei für den Dateitresor
 * 
 * Diese Datei enthält alle konfigurierbaren Einstellungen für den Dateitresor.
 * Änderungen an der Funktionalität sollten hier vorgenommen werden.
 */

// WordPress-Pfad
define('WP_CORE_PATH', dirname(__DIR__) . '/wordpress/wp-load.php');

// Rollenzuordnungen
$role_mappings = [
    'subscriber' => 'group-1',
    'contributor' => 'group-2'
];

// Download-Einstellungen
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB

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
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];

// Cache-Einstellungen
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Sicherheits-Header
define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\''
]);

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false); 