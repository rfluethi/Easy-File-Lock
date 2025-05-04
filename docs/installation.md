# Installation Guide

## Overview

This guide provides a step-by-step walkthrough for installing and setting up a role-based file sharing system using WordPress.  
It is intended for technically proficient users with access to the server's file system and a basic understanding of WordPress and PHP.

## Quick Start

1. **Download ZIP Files**

   - `protected.zip`
   - `secure-files.zip`

2. **Extract and Copy Files**

   - Copy the contents of `protected.zip` into the **WebRoot directory**
   - Place the contents of `secure-files.zip` in a directory **outside the WebRoot**

   **What is the WebRoot directory?**  
   The WebRoot is the publicly accessible root directory of your web server – e.g., `htdocs`, `www`, `html`, or `public_html`. Files outside this directory are not directly accessible via a browser.

3. **Modify WordPress Configuration**

   Add the following line to your `wp-config.php` file:

   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

## Requirements

**System Requirements:**

- Apache 2.4 or later with `mod_rewrite` enabled
- PHP 7.4 or later
- At least 128 MB PHP memory limit
- Write permissions for the log directory
- Sufficient storage space for protected files

**PHP Extensions:**

- `fileinfo`
- `mbstring`
- `openssl`

**WordPress:**

- Version 5.0 or later
- User roles enabled
- Write permissions in the WordPress directory

## Server Directory Structure

After installation, your directory structure should look like this:

```txt
/var/www/
├── html/                      ← WebRoot (publicly accessible)
│   ├── wordpress/             ← WordPress installation
│   │   └── wp-load.php
│   └── protected/             ← from protected.zip, within WebRoot
│       ├── .htaccess
│       └── check-access.php
└── secure-files/              ← from secure-files.zip, outside WebRoot
    ├── config/
    │   └── secure-config.php
    ├── logs/
    │   └── access.log
    ├── group-1/
    │   └── example-1.pdf
    └── group-2/
        └── example-2.pdf
```

## Set File Permissions

**Directories:**

- `secure-files`, `config`, `logs`, `group-*`: Read and execute permissions (e.g., `755`)

**Files:**

- `secure-config.php`, `access.log`, files in `group-*`: Read permissions (e.g., `644`)

## Configuration

### a) WordPress Configuration (`wp-config.php`)

```php
define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
```

### b) Secure Configuration (`secure-files/config/secure-config.php`)

```php
// Path to the WordPress installation
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');
// Alternatively, if WordPress is located directly in WebRoot:
// define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');

// Log directory and file
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');

// Role assignment
$role_folders = [
    'subscriber'  => 'group-1',
    'contributor' => 'group-2'
];

// Download settings
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576); // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MIN_MEMORY_LIMIT', '128M');
```

## Testing the Installation

1. Log in to WordPress as a registered user
2. Open a protected file in the browser, e.g.:
   `https://your-domain.com/protected/group-1/example-1.pdf`
3. Check whether access was logged:
   The log file is located at `secure-files/logs/access.log` and can be accessed via FTP.

## Troubleshooting

**"Undefined constant MIN_MEMORY_LIMIT"**

- Is `secure-config.php` included properly?
- Is the file path specified correctly?
- Is the file present and readable?

**"WordPress not found"**

- Check the path in `secure-config.php`
- Is `wp-load.php` located at the specified location?
- Are permissions set correctly?

**"Configuration file not found"**

- Is `secure-files` located outside the WebRoot?
- Is the directory structure set up correctly?
- Are read permissions in place?

**To diagnose errors, check the file `secure-files/logs/access.log`.**  
It is accessible via FTP and contains information on access attempts.

**More potential errors** are documented in the file **[Troubleshooting](troubleshooting.md)**.

## Security Notes

- **Directory structure:**  
  The `secure-files` directory must be placed outside the WebRoot.  
  Only the `protected` directory should be publicly accessible.

- **File permissions:**  
  Use minimal permissions. Review and adjust them regularly.

- **Debug mode:**  
  Enable only in development environments. Disable in production.

- **System maintenance:**  
  Keep WordPress and PHP up to date. Regularly review server configuration and security headers.

**More security-related guidance** can be found in the file **[Security Policies](security.md)**.