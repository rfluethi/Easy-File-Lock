# Troubleshooting Guide

This guide assists administrators and developers in diagnosing and resolving common issues in the protected file access system.

## Common Issues

### Access Issues

#### Problem: User cannot access files

**Steps to resolve:**

1. Open `secure-config.php` and check the role mappings:

   ```php
   $role_mappings = [
       'subscriber' => 'group-1',
       'contributor' => 'group-2'
   ];
   ```
2. Ensure the user has the correct role in WordPress.
3. Check directory permissions (at least 755 for folders, 644 for files).
4. If needed, consult the log file at: `secure-files/logs/access.log`

#### Problem: File not found

**Steps to resolve:**

1. Verify the URL (e.g., `/protected/group-1/example-1.pdf`).
2. Ensure the file exists in the correct group directory.
3. Check if the file is correctly named and readable (permission 644).

### Error 404 – File Not Found

**Possible causes:**

* Incorrect URL
* File in the wrong folder
* Incorrect `SECURE_FILE_PATH` constant

**Solutions:**

1. Compare the URL with the server structure:

   ```
   /secure-files/group-1/example-1.pdf  // for subscriber
   /secure-files/group-2/example-2.pdf  // for contributor
   ```
2. Check in `wp-config.php`:

   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```
3. Check for typos in the filename.

### Error 403 – Access Denied

**Possible causes:**

* Missing or incorrect user role
* Role not registered in the access script
* File in the wrong directory

**Solutions:**

1. Check the user's role in WordPress.
2. Review the role configuration in `secure-config.php`:

   ```php
   $role_folders = [
       'subscriber' => 'group-1',
       'contributor' => 'group-2'
   ];
   ```
3. Ensure the file and role match appropriately.

### Infinite Redirect (Loop)

**Possible causes:**

* Cookie conflict between www/non-www
* Incorrect path to `wp-load.php`
* Invalid WordPress session

**Solutions:**

1. Check domain configuration in WordPress.
2. Verify in `check-access.php`:

   ```php
   require_once WP_CORE_PATH;
   ```
3. Clear browser cache and cookies.

## Download Issues

### Interrupted Downloads

**Causes:**

* PHP timeout exceeded
* Inappropriate chunk size
* Network issues / timeouts

**Solutions:**

1. Adjust `CHUNK_SIZE` and `MAX_DIRECT_DOWNLOAD_SIZE` in `secure-config.php`:

   ```php
   define('CHUNK_SIZE', 4194304);              // 4 MB
   define('MAX_DIRECT_DOWNLOAD_SIZE', 524288); // 512 KB
   ```
2. Check server timeout settings (`max_execution_time`).

### Incorrect MIME Types

**Symptoms:**

* File opens in the browser instead of downloading
* Error: "unsupported format"

**Solutions:**

1. Add missing MIME types in `secure-config.php`:

   ```php
   $allowed_mime_types = [
       'pdf' => 'application/pdf',
       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
       'html' => 'text/html'
   ];
   ```
2. Verify file extensions and type consistency.

## Server Issues

### Error 500 – Internal Server Error

**Possible causes:**

* Invalid PHP configuration
* Incorrect permissions
* Missing PHP extensions

**Solutions:**

1. Enable debug mode:

   ```php
   define('DEBUG_MODE', true);
   ```
2. Check permissions:

   ```
   secure-files: 755
   config: 755
   secure-config.php: 644
   ```
3. Ensure required extensions (`fileinfo`, `mbstring`, `openssl`) are active.

### Slow Downloads / Performance

**Causes:**

* Chunk size too small
* High concurrent load
* Server resource limits

**Solutions:**

1. Gradually increase `CHUNK_SIZE`.
2. Use server-side rate limiting.
3. Analyze server load (RAM, CPU, Disk I/O).

### Cache Issues

**Symptoms:**

* File changes not reflected
* Old versions shown in browser

**Solutions:**

1. Check cache-control settings:

   ```php
   define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');
   ```
2. Clear browser cache; disable caching plugins if necessary.

## Quick Checklist: Access Errors

* [ ] Is the user logged in?
* [ ] Is the role configured correctly?
* [ ] Is the file in the correct group folder?
* [ ] Is the MIME type allowed?
* [ ] Are permissions set correctly?
* [ ] Is access being logged in `access.log`?

## Note

For advanced diagnostics, regularly review the log file:

```
/secure-files/logs/access.log
```

This file contains information about blocked requests, access attempts, and error messages.