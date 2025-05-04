<?php
/**
 * check-access.php  —  role-aware download gate
 * Access control for protected files
 * Must be placed inside the WebRoot directory (e.g. protected/)
 */

// WordPress config
define('WP_USE_THEMES', false);

// Load configuration first!
$config_file = dirname(dirname(__DIR__)) . '/secure-files/config/secure-config.php';
if (!file_exists($config_file)) {
    die('Error: Configuration file not found. Check installation.');
}
require_once $config_file;

// Now all constants are available
$memory_limit = ini_get('memory_limit');
if (intval($memory_limit) < intval(MIN_MEMORY_LIMIT)) {
    die('PHP memory limit too low. Minimum required: ' . MIN_MEMORY_LIMIT);
}

// Load WordPress
if (!file_exists(WP_CORE_PATH)) {
    die('Error: WordPress not found. Check WP_CORE_PATH in secure-config.php.');
}
require_once WP_CORE_PATH;

// Enable debug output if active
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Define secure file path if not already defined
if (!defined('SECURE_FILE_PATH')) {
    define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
}

// Ensure log directory and file exist
if (!is_dir(LOG_DIR)) {
    if (!mkdir(LOG_DIR, 0755, true)) {
        die('Could not create log directory');
    }
}
if (!file_exists(LOG_FILE)) {
    if (!touch(LOG_FILE)) {
        die('Could not create log file');
    }
    chmod(LOG_FILE, 0644);
}

// Rotate logs if size exceeds limit
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

// Log current roles if debug is active
$current = wp_get_current_user();
if (DEBUG_MODE) {
    file_put_contents(LOG_FILE, 'Current roles: ' . print_r($current->roles, true) . PHP_EOL, FILE_APPEND);
}

// Block unauthenticated users
if (!is_user_logged_in()) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, 'User not logged in' . PHP_EOL, FILE_APPEND);
    }
    auth_redirect();
    exit;
}

// Get and sanitize requested file path
$rel = $_GET['file'] ?? '';
$rel = ltrim(str_replace(['..', './', '\\'], '', $rel), '/');
if ($rel === '' || substr($rel, -1) === '/') {
    $rel .= 'index.html';
}

// Validate file path pattern
if (!preg_match('/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/', $rel)) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Invalid filename: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(400);
    exit('Invalid filename');
}

// Role-based access check
$roles = $current->roles;
$allowed = false;
foreach ($roles as $role) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Checking role: $role" . PHP_EOL, FILE_APPEND);
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
        file_put_contents(LOG_FILE, "Access denied for: $rel" . PHP_EOL, FILE_APPEND);
        file_put_contents(LOG_FILE, "User roles: " . implode(', ', $roles) . PHP_EOL, FILE_APPEND);
    }
    status_header(403);
    exit('Forbidden');
}

// Resolve absolute file path and validate it
$abs = realpath(SECURE_FILE_PATH . '/' . $rel);
if (
    $abs === false ||
    !is_file($abs) ||
    strncmp($abs, SECURE_FILE_PATH, strlen(SECURE_FILE_PATH)) !== 0
) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "File not found: $rel" . PHP_EOL, FILE_APPEND);
        file_put_contents(LOG_FILE, "Resolved path: $abs" . PHP_EOL, FILE_APPEND);
    }
    status_header(404);
    exit('not found');
}

// Get file size and check limits
$size = filesize($abs);
if ($size === false) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Could not read size: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(500);
    exit('Internal server error');
}
if ($size > MAX_FILE_SIZE) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "File too large: $rel ($size Bytes)" . PHP_EOL, FILE_APPEND);
    }
    status_header(413);
    exit('File too large');
}

// Determine MIME type and set headers
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$mime = $allowed_mime_types[$ext] ?? 'application/octet-stream';

header("Content-Type: $mime");
header('Cache-Control: ' . CACHE_CONTROL);
foreach (SECURITY_HEADERS as $header) {
    header($header);
}
header("Content-Length: $size");

// Log transfer start
if (DEBUG_MODE) {
    file_put_contents(LOG_FILE, sprintf(
        "Sending file: %s (%s, %d Bytes)" . PHP_EOL,
        $rel,
        $mime,
        $size
    ), FILE_APPEND);
}

// Send large file in chunks
if ($size > MAX_DIRECT_DOWNLOAD_SIZE) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Starting chunked download" . PHP_EOL, FILE_APPEND);
    }
    @set_time_limit(0);
    while (ob_get_level()) ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp === false) {
        if (DEBUG_MODE) {
            file_put_contents(LOG_FILE, "Failed to open file: $rel" . PHP_EOL, FILE_APPEND);
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
                file_put_contents(LOG_FILE, "Read error: $rel" . PHP_EOL, FILE_APPEND);
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
                "Chunk %d: %d Bytes sent" . PHP_EOL,
                $chunks,
                $total
            ), FILE_APPEND);
        }
    }
    fclose($fp);
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, sprintf(
            "Download finished: %d Chunks, %d Bytes" . PHP_EOL,
            $chunks,
            $total
        ), FILE_APPEND);
    }
    exit;
}

// Direct download for small files
$result = readfile($abs);
if ($result === false) {
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, "Readfile failed: $rel" . PHP_EOL, FILE_APPEND);
    }
    status_header(500);
    exit('Internal server error');
}
exit;