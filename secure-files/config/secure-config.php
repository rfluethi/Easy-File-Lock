<?php
/**
 * Configuration file for the secure file vault
 * 
 * This file defines all configurable settings for the protected file access system.
 * Modify these constants and variables to adapt functionality to your environment.
 */

// WordPress core path configuration
// Define the path to the WordPress bootstrap file (wp-load.php) for system integration.
// Adjust the path depending on whether WordPress is in a subdirectory or the WebRoot.
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');  // Use if WordPress is installed in /wordpress/
// Alternative for WebRoot installs:
// define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');

// Logging configuration
// Defines the location, maximum size, and rotation policy for access logs.
define('LOG_DIR', dirname(__DIR__) . '/logs');                  // Directory where log files are stored
define('LOG_FILE', LOG_DIR . '/access.log');                    // Primary log file name
define('LOG_MAX_SIZE', 5 * 1024 * 1024);                        // Maximum log file size before rotation (5 MB)
define('LOG_MAX_FILES', 5);                                     // Maximum number of rotated log files to retain

// Role-to-folder mapping
// Maps WordPress user roles to specific directories for file access.
// Only users with the matching role can access the contents of the corresponding folder.
$role_folders = [
    'subscriber' => 'group-1',    // Subscribers can access files in /group-1
    'contributor' => 'group-2'    // Contributors can access files in /group-2
];

// Download configuration
// Controls size thresholds and chunking behavior for file transfers.
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);   // Max size (1 MB) for direct transfer without chunking
define('CHUNK_SIZE', 4194304);                 // Chunk size (4 MB) for streamed downloads
define('MAX_FILE_SIZE', 1024 * 1024 * 1024);   // Maximum allowed file size (1 GB)
define('MIN_MEMORY_LIMIT', '128M');            // Minimum required PHP memory limit for processing downloads

// Allowed MIME types for file delivery
// Whitelists file extensions and their corresponding MIME types to prevent serving unsafe formats.
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

// Cache control policy
// Prevents client-side and proxy caching to ensure access is always verified server-side.
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Security headers
// These HTTP headers enhance browser-level security and mitigate common web vulnerabilities.
define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',                                 // Prevent MIME-sniffing attacks
    'X-Frame-Options: DENY',                                           // Disallow framing to prevent clickjacking
    'X-XSS-Protection: 1; mode=block',                                 // Enable basic XSS protection
    'Referrer-Policy: strict-origin-when-cross-origin',               // Restrict referrer information
    'Content-Security-Policy: default-src \'self\'',                  // Limit resource loading to same origin
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'  // Enforce HTTPS usage for one year
]);

// Debug mode (for development only)
// Enables verbose error output and extensive logging; should be disabled in production.
define('DEBUG_MODE', true);
