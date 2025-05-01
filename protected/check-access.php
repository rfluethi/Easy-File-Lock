<?php
/**
 * check-access.php  —  role-aware download gate
 */

define('WP_USE_THEMES', false);         // skip WP theme bootstrap
require_once dirname(__DIR__) . '/main/wp-load.php';    // load WordPress core

// vault root (fallback if constant missing)
if (!defined('SECURE_FILE_PATH')) {
    define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
}

// role → folder map (loaded from secure-config.php)
$role_folders = require SECURE_FILE_PATH . '/config/secure-config.php';

// only logged-in users
if (!is_user_logged_in()) {
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
    status_header(404);
    exit('not found');
}

// MIME + headers
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$mime = [
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
][$ext] ?? 'application/octet-stream';

header("Content-Type: $mime");
header('X-Content-Type-Options: nosniff');
$size = filesize($abs);
header("Content-Length: $size");

// send file (chunked for large payloads)
if ($size > 524288) {                // > 512 kB
    @set_time_limit(0);
    while (ob_get_level())
        ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp) {
        while (!feof($fp)) {
            echo fread($fp, 1048576);
            flush();
        }
        fclose($fp);
    }
    exit;
}
readfile($abs);
exit;
