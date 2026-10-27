# Configuration Documentation

This documentation outlines the configuration of a secure file delivery system based on WordPress roles. It is intended for technically proficient users with server access and a basic understanding of PHP and WordPress.

## Important Notes

### Load Order

The load order of components is critical:

1. `secure-config.php` must be loaded before any other components.
2. Constants such as `MIN_MEMORY_LIMIT` must only be used after this file is included.
3. WordPress should then be loaded via `wp-load.php`.

### Access Control

The system implements a hierarchical access control:

1. **Administrator Access**
   - Users with the 'administrator' role have full access to all protected files
   - This is a built-in feature that cannot be disabled
   - Administrators can access files in all group directories

2. **Role-Based Access**
   - Other users can only access files in their assigned group directories
   - Access is controlled by the `$role_folders` configuration

### Error Handling

If `secure-config.php` is missing or cannot be loaded, the script must terminate with an appropriate error message. The same applies if the WordPress path (`WP_CORE_PATH`) is not correctly set or if `wp-load.php` cannot be found.

## File Transfer Settings

```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimum PHP memory limit
```

**Explanation:**

* `MAX_DIRECT_DOWNLOAD_SIZE`: Files up to this size are sent directly. Larger files are streamed.
* `CHUNK_SIZE`: Size of data chunks during streaming. Smaller values slow down delivery; larger ones risk memory issues.
* `MAX_FILE_SIZE`: Maximum allowed file size.
* `MIN_MEMORY_LIMIT`: Minimum required PHP memory limit.

## Logging Settings

```php
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('LOG_MAX_FILES', 5);               // Max. 5 log files
define('DEBUG_MODE', false);              // Must be false in production. Set to true only for short-term troubleshooting.
```

**Explanation:**

* `LOG_DIR`: Directory for log files.
* `LOG_FILE`: Path to the log file.
* `LOG_MAX_SIZE`: Log rotation is triggered once this size is reached.
* `LOG_MAX_FILES`: Maximum number of retained log files.
* `DEBUG_MODE`: Enables additional debug output (recommended during development; disable in production).

## Security Settings

```php
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\'',
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'
]);
```

**Explanation:**

* `CACHE_CONTROL`: Controls caching behavior for file downloads.
* `SECURITY_HEADERS`: Array of security-related HTTP headers to protect against common attack vectors.

## Role-Based Directory Access

```php
$role_folders = [
    'subscriber'  => 'group-1',              // single folder
    'contributor' => 'group-2',              // single folder
    // 'editor'   => ['group-1', 'group-3'], // multiple folders as array
];
```

* `subscriber` has access to the `group-1` directory.
* `contributor` has access to `group-2`.
* A role can be mapped to a **single folder** (string) or **multiple folders** (array). Both syntaxes are valid and can be mixed within the same configuration.
* If multiple roles should access the same directory, assign the same target folder to each role.

## User-Based Directory Access

Individual users can be mapped to a folder by WordPress user ID. This takes priority over role-based access — if a user ID is listed in `$user_folders`, **only** that folder is accessible (the role mapping is not applied as a fallback).

```php
$user_folders = [
    42 => 'client-john',   // User with ID 42 may access /client-john
    99 => 'client-jane',   // User with ID 99 may access /client-jane
];
```

**How to find a user's ID:** In the WordPress admin go to Users → All Users and hover over a username. The URL shown in the browser status bar contains `?user_id=42`.

**Access priority (highest to lowest):**
1. Administrator role → full access to all files.
2. Entry in `$user_folders` → access to the specified folder only (exclusive).
3. Entry in `$role_folders` → access based on WordPress role.

## Allowed MIME Types for Downloads

```php
$allowed_mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
];

$allowed_mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
```

Only file types defined in `$allowed_mime_types` are eligible for download. Additional types can be dynamically appended.

**Behavior for Allowed MIME Types:**

* Files are made available for download, provided path, user role, and other conditions are met.

**Behavior for Disallowed MIME Types:**

* The download is blocked.
* Access is denied, and the request is logged (if logging is enabled).
* No file data is sent to the client.

## Configuration Advice

These configuration values should be tailored to your specific server environment. In production environments, `DEBUG_MODE` should be set to `false`. Security headers and role assignments should be reviewed and updated regularly as needed.