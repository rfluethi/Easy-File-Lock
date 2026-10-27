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

/**
 * Convert PHP shorthand byte values (e.g. '128M', '1G') to bytes.
 * Returns PHP_INT_MAX for unlimited (-1) or empty values.
 * intval() alone is wrong: intval('1G') === 1, which is far less than 128 (MB default).
 */
function efl_to_bytes($value) {
    $value = trim((string) $value);
    if ($value === '' || $value === '-1') {
        return PHP_INT_MAX;
    }
    $unit = strtolower(substr($value, -1));
    $num  = (int) $value;
    switch ($unit) {
        case 'g': return $num * 1024 * 1024 * 1024;
        case 'm': return $num * 1024 * 1024;
        case 'k': return $num * 1024;
        default:  return (int) $value;
    }
}

// Now all constants are available
if (efl_to_bytes(ini_get('memory_limit')) < efl_to_bytes(MIN_MEMORY_LIMIT)) {
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
    // Set umask before touch() to prevent a brief window where the file
    // might exist with group- or world-write permissions before chmod() runs.
    $prev_umask = umask(0133); // results in 0644 at creation
    $created = touch(LOG_FILE);
    umask($prev_umask);
    if (!$created) {
        die('Could not create log file');
    }
    chmod(LOG_FILE, 0644);
}

/**
 * Anonymise an IP address for privacy-compliant logging.
 * IPv4: last octet replaced with 'xxx'  (192.168.1.123 → 192.168.1.xxx)
 * IPv6: last 64 bits zeroed via inet_pton/inet_ntop (2001:db8::1 → 2001:db8::)
 * Returns '-' for invalid or empty input.
 */
function efl_anonymize_ip($ip) {
    $ip = trim((string) $ip);
    if ($ip === '') {
        return '-';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return substr($ip, 0, strrpos($ip, '.')) . '.xxx';
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $bin = inet_pton($ip);
        if ($bin === false) {
            return '-';
        }
        $masked = substr($bin, 0, 8) . str_repeat("\x00", 8);
        return inet_ntop($masked);
    }
    return '-';
}

/**
 * Append a line to the access log.
 * - level 'access' is always written (audit trail independent of DEBUG_MODE)
 * - level 'debug' is only written when DEBUG_MODE is true
 */
function efl_log($level, $message) {
    if ($level === 'debug' && !DEBUG_MODE) {
        return;
    }
    $user_id = function_exists('wp_get_current_user') && wp_get_current_user()->ID
        ? (int) wp_get_current_user()->ID
        : '-';
    $line = sprintf(
        "[%s] [%s] [user=%s] [ip=%s] %s%s",
        date('c'),
        strtoupper($level),
        $user_id,
        efl_anonymize_ip($_SERVER['REMOTE_ADDR'] ?? ''),
        $message,
        PHP_EOL
    );
    $written = @file_put_contents(LOG_FILE, $line, FILE_APPEND);
    if ($written === false && $level === 'access') {
        error_log('efl_log: failed to write access log entry: ' . trim($line));
    }
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

// Load user object and log current roles
$current = wp_get_current_user();
efl_log('debug', 'Current roles: ' . print_r($current->roles, true));

// Block unauthenticated users
if (!is_user_logged_in()) {
    efl_log('debug', 'User not logged in');
    auth_redirect();
    exit;
}

// Get and sanitize requested file path
$rel = $_GET['file'] ?? '';
$rel = ltrim((string) $rel, '/');
if ($rel === '' || substr($rel, -1) === '/') {
    $rel .= 'index.html';
}

// Component whitelist: every path component must validate strictly.
// This replaces the previous blacklist-based str_replace approach, which was
// non-idempotent and fragile — realpath() caught traversal attempts, but
// clean input validation at the gate is the correct first line of defense.
// The first character must not be a dot, so dotfiles (.env, .htaccess, .git)
// are rejected here before the MIME whitelist even gets a chance to see them.
$parts = explode('/', $rel);
foreach ($parts as $p) {
    if ($p === '' || $p === '.' || $p === '..' || !preg_match('/^[A-Za-z0-9_-][A-Za-z0-9._-]*$/', $p)) {
        efl_log('access', "DENY 400 file=$rel reason=invalid_component");
        status_header(400);
        exit('Invalid filename');
    }
}

// Role-based access check
$normalize_key = static function ($value) {
    $value = strtolower((string)$value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
};

$normalized_role_folders = [];
foreach ($role_folders as $role_key => $folder) {
    $normalized_role = $normalize_key($role_key);
    if ($normalized_role === '') {
        continue;
    }
    // Accept both a single string ('group-1') and an array (['group-1', 'group-3']).
    // Normalise to an array of non-empty strings so the access check is uniform.
    $folders = is_array($folder) ? $folder : [$folder];
    $clean = [];
    foreach ($folders as $f) {
        if (is_string($f) && $f !== '') {
            $clean[] = ltrim($f, '/');
        }
    }
    if (!empty($clean)) {
        $normalized_role_folders[$normalized_role] = $clean;
    }
}

$roles = is_array($current->roles) ? $current->roles : [];
if (empty($roles) && isset($current->caps) && is_array($current->caps)) {
    $roles = array_keys(array_filter($current->caps));
}

$allowed = false;
$user_id = (int) $current->ID;

// Step 1: Administrator check — always full access, regardless of any mapping.
// Strict literal comparison to prevent custom roles whose slug normalises to
// 'administrator' (e.g. 'admin-istrator-x') from gaining unintended access.
foreach ($roles as $role) {
    if ($role === 'administrator') {
        $allowed = true;
        efl_log('debug', "Administrator access granted: user_id=$user_id");
        break;
    }
}

// Step 2: User-specific folder check (takes priority over role mapping).
// If the user ID has an entry in $user_folders, ONLY that folder is accessible —
// there is no fallback to role-based access. This keeps per-user grants explicit
// and prevents unexpected cross-folder access via a secondary role.
if (!$allowed && isset($user_folders[$user_id])) {
    $allowed_folder = ltrim((string) $user_folders[$user_id], '/');
    efl_log('debug', "User-specific mapping found: user_id=$user_id folder=$allowed_folder");
    if ($rel === $allowed_folder || strpos($rel, $allowed_folder . '/') === 0) {
        $allowed = true;
    } else {
        efl_log('access', "DENY 403 file=$rel reason=outside_user_folder user_id=$user_id");
        status_header(403);
        exit('Forbidden');
    }
}

// Step 3: Role-based check — only reached if the user has no entry in $user_folders.
if (!$allowed) {
    foreach ($roles as $role) {
        efl_log('debug', "Checking role: $role (normalized: " . $normalize_key($role) . ")");
        $normalized_user_role = $normalize_key($role);
        if (isset($normalized_role_folders[$normalized_user_role])) {
            foreach ($normalized_role_folders[$normalized_user_role] as $allowed_folder) {
                if ($rel === $allowed_folder || strpos($rel, $allowed_folder . '/') === 0) {
                    $allowed = true;
                    break 2;
                }
            }
        }
    }
}

if (!$allowed) {
    efl_log('access', "DENY 403 file=$rel roles=" . implode(',', $roles));
    efl_log('debug', "Normalized role mapping: " . json_encode($normalized_role_folders));
    status_header(403);
    exit('Forbidden');
}

// Resolve absolute file path and validate it
$secure_root = realpath(SECURE_FILE_PATH);
$abs = realpath(SECURE_FILE_PATH . '/' . $rel);
if (
    $secure_root === false ||
    $abs === false ||
    !is_file($abs) ||
    strpos($abs, $secure_root . DIRECTORY_SEPARATOR) !== 0
) {
    efl_log('debug', "File not found: $rel");
    efl_log('debug', "Resolved path: $abs");
    efl_log('debug', "Secure root: $secure_root");
    efl_log('debug', "Configured root: " . SECURE_FILE_PATH);
    status_header(404);
    exit('not found');
}

// Get file size and check limits
$size = filesize($abs);
if ($size === false) {
    efl_log('debug', "Could not read size: $rel");
    status_header(500);
    exit('Internal server error');
}
if ($size > MAX_FILE_SIZE) {
    efl_log('debug', "File too large: $rel ($size bytes)");
    status_header(413);
    exit('File too large');
}

// Determine MIME type — hard reject any extension not in the whitelist.
// The previous fallback to 'application/octet-stream' would silently serve
// files with unlisted extensions (e.g. .env, .bak, .sql) with a 200 OK.
$ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
if (!isset($allowed_mime_types[$ext])) {
    efl_log('access', "DENY 415 file=$rel reason=ext_not_whitelisted");
    status_header(415);
    exit('Unsupported file type');
}
$mime = $allowed_mime_types[$ext];

// Send global security headers first — always, for every file type.
header("Content-Type: $mime");
header('Cache-Control: ' . CACHE_CONTROL);
foreach (SECURITY_HEADERS as $header) {
    header($header);
}
// Per-extension CSP overrides the Content-Security-Policy from SECURITY_HEADERS.
// HTML and SVG get a sandboxed policy so inline content cannot access
// cookies of the WordPress origin.
if (isset($csp_per_extension[$ext])) {
    header('Content-Security-Policy: ' . $csp_per_extension[$ext]);
}
// Block cross-site embedding via <img>, <script>, <link> etc.
// An attacker could detect whether a file exists by loading it cross-origin
// and observing onload/onerror events. same-origin prevents the browser
// from completing the cross-site load.
header('Cross-Origin-Resource-Policy: same-origin');
header("Content-Length: $size");

// Log successful access (always written, independent of DEBUG_MODE)
efl_log('access', "ALLOW 200 file=$rel size=$size mime=$mime");
efl_log('debug', sprintf("Sending file: %s (%s, %d bytes)", $rel, $mime, $size));

// Send large file in chunks
if ($size > MAX_DIRECT_DOWNLOAD_SIZE) {
    efl_log('debug', "Starting chunked download");
    @set_time_limit(0);
    while (ob_get_level()) ob_end_flush();
    $fp = fopen($abs, 'rb');
    if ($fp === false) {
        efl_log('debug', "Failed to open file: $rel");
        status_header(500);
        exit('Internal server error');
    }
    $chunks = 0;
    $total = 0;
    while (!feof($fp)) {
        $data = fread($fp, CHUNK_SIZE);
        if ($data === false) {
            efl_log('debug', "Read error: $rel");
            fclose($fp);
            status_header(500);
            exit('Internal server error');
        }
        echo $data;
        flush();
        $chunks++;
        $total += strlen($data);
        if ($chunks % 10 === 0) {
            efl_log('debug', sprintf("Chunk %d: %d bytes sent", $chunks, $total));
        }
    }
    fclose($fp);
    efl_log('debug', sprintf("Download finished: %d chunks, %d bytes", $chunks, $total));
    exit;
}

// Direct download for small files
$result = readfile($abs);
if ($result === false) {
    efl_log('debug', "Readfile failed: $rel");
    status_header(500);
    exit('Internal server error');
}
exit;
