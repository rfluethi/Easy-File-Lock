# Security Policies

This document outlines the security measures and best practices for protecting files.

## Objectives of Security Measures

The following measures aim to prevent:

* Unauthorized access to protected files
* Bypassing user roles and access restrictions
* Directory traversal attacks and direct file access
* Abuse by automated tools or mass access (DoS)
* Application-layer vulnerabilities (e.g., XSS, CSRF)
* Violations of data protection regulations (e.g., GDPR)

## Security Architecture

### Layered Security

1. **File System Level**

   * Files stored outside the WebRoot
   * Strict permissions
   * No direct URL accessibility

2. **Web Server Level**

   * `.htaccess` protection
   * PHP execution disabled
   * Redirects to `check-access.php`

3. **Application Level**

   * WordPress authentication
   * Role-based access control
   * Session management
   * XSS protection (escape HTML output)
   * CSRF protection in forms (e.g., token usage)

4. **Transport Layer**

   * HTTPS encryption
   * Secure cookies
   * Chunked downloads

## Security Measures

### File System Security

1. **Folder Structure**

   ```
   /secure-files/              # Main directory (755)
   ├── config/                # Configuration folder (755)
   │   └── secure-config.php  # Configuration file (644)
   └── [role-folders]/        # Role-specific folders (755)
   ```

2. **Permissions**

   * Folders: 755 (drwxr-xr-x)
   * Files: 644 (-rw-r--r--)
   * Executable files: 755 (-rwxr-xr-x)

3. **Protection Measures**

   * No PHP execution
   * No directory listing
   * No direct downloads

### Web Server Security

1. **.htaccess Rules**

   The `protected/.htaccess` file contains a fail-closed block that denies all access if `mod_rewrite` is not loaded — there is no silent fallback that would expose files directly.

   ```apache
   # Block dotfiles (.git, .env, .htaccess) from being served
   <FilesMatch "^\.">
       Require all denied
   </FilesMatch>

   # Fail-closed: if mod_rewrite is unavailable, deny everything.
   <IfModule !mod_rewrite.c>
       Require all denied
   </IfModule>

   <IfModule mod_rewrite.c>
     RewriteEngine On
     RewriteBase /protected/

     # No directory listing
     Options -Indexes
     # No CGI / SSI execution
     Options -ExecCGI -Includes

     # Block direct browser access to the serving script
     RewriteCond %{THE_REQUEST} \s/+protected/check-access\.php(?:[\s?]|$) [NC]
     RewriteRule ^check-access\.php$ - [F,L]

     # Directory request → index.html
     RewriteRule ^(.+/)$ /protected/check-access.php?file=$1index.html [QSA,L]

     # Route every file request through the PHP gatekeeper
     RewriteRule ^(.+)$ /protected/check-access.php?file=$1 [QSA,L]
   </IfModule>

   <IfModule mod_headers.c>
     Header set Cache-Control "private, no-cache, no-store, must-revalidate"
   </IfModule>
   ```

2. **PHP Configuration**

   * `display_errors = Off`
   * `log_errors = On`
   * `error_reporting = E_ALL`

3. **Rate Limiting and Abuse Protection (recommended)**

   Easy File Lock itself does not implement rate limiting — enforce it at the web server or network layer:

   * Apache: `mod_evasive` or `mod_ratelimit`
   * Nginx: `limit_req_zone` / `limit_req`
   * Cloud/WAF: Cloudflare Rate Limiting, AWS WAF, etc.

   Without server-level rate limiting, the PHP gatekeeper (and WordPress bootstrap) will execute on every unauthenticated request.

### Application Security

1. **Authentication**

   * WordPress login required
   * Strong password policies
   * Session timeouts

2. **Access Control**

   * Role-based permissions via `$role_folders`
   * User-based permissions via `$user_folders` (takes priority over role mapping)
   * Folder-based restrictions
   * No role escalation

3. **File Handling**

   * MIME type validation
   * File size restrictions
   * Chunked downloads

### CSP Sandbox for HTML and SVG

HTML and SVG responses are served with `Content-Security-Policy: sandbox …`. This disables script execution in the rendered document and treats it as a unique origin, so embedded code cannot read WordPress cookies or call `wp-admin` endpoints with credentials.

If a specific HTML deliverable must run its own JavaScript, extend the sandbox token list in `secure-config.php`:

```php
$csp_per_extension['html'] =
    "sandbox allow-forms allow-popups allow-scripts; "
    . "default-src 'self'; img-src 'self' data:; "
    . "style-src 'self' 'unsafe-inline'; font-src 'self' data:; "
    . "script-src 'self'; object-src 'none'; base-uri 'none';";
```

`allow-same-origin` is intentionally absent from the default — adding it re-exposes the WordPress origin to anything the HTML file ships.

### Cross-Site Embedding and SameSite Cookies

Every file response includes `Cross-Origin-Resource-Policy: same-origin`, which prevents browsers from completing cross-site loads via `<img>`, `<script>`, or `<link>`. This removes a low-severity information-disclosure vector where an attacker could probe file existence by observing `onload`/ `onerror` events.

For full protection, ensure WordPress authentication cookies carry `SameSite=Lax` (the default since WordPress 5.7) or `SameSite=Strict`. Verify your cookie flags with browser DevTools or `curl -I`.

### Data Protection & Logging

* IP addresses are anonymised before logging: IPv4 last octet replaced with `xxx`, IPv6 last 64 bits zeroed (via `efl_anonymize_ip()`). No full IP addresses are stored.
* Log file access restricted to authorized administrators (stored outside WebRoot)
* Define a retention policy and automate deletion of old logs (e.g., after 30 days via cron)

## Best Practices

### Configuration

1. **Secure Settings**

   ```php
   define('DEBUG_MODE', false);          // Disable debug mode
   define('CHUNK_SIZE', 4194304);        // 4 MB
   define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576); // 1 MB
   ```

2. **MIME Types**

   * Define only allowed types
   * Exclude executable files (e.g., .php, .exe)
   * Use documented and verifiable formats

### Maintenance

1. **Regular Checks**

   * Review permissions
   * Analyze logs
   * Apply updates

2. **Backup Strategy**

   * Regular backups
   * Encrypted storage
   * Separate locations

3. **Monitoring**

   * Access logs
   * Error logs
   * Performance metrics

## Security Checklist

### Installation

* [ ] Folders located outside the WebRoot
* [ ] Correct permissions for files and directories
* [ ] No executable files in the download directory
* [ ] HTTPS fully configured
* [ ] Debug mode disabled
* [ ] Access only via `check-access.php`

### Configuration

* [ ] User roles correctly assigned
* [ ] Defined access structure per role
* [ ] MIME types restricted to safe formats
* [ ] Appropriate chunk size configured
* [ ] Download limits set (size and number)
* [ ] Security headers active
* [ ] PHP error output disabled

### Maintenance

* [ ] Regular updates of WordPress, PHP, and plugins
* [ ] Log file analysis and rotation
* [ ] Regular permission reviews
* [ ] Backup plan tested and documented
* [ ] Monitoring enabled (errors, access, performance)
* [ ] Old logs deleted in compliance with data protection laws

## Incident Response

### In Case of Security Incidents

1. **Immediate Actions**

   * Isolate the system
   * Secure the logs
   * Revoke access credentials

2. **Analysis**

   * Identify root cause
   * Assess impact
   * Document the incident

3. **Recovery**

   * Clean the system
   * Apply updates and fixes
   * Reconfigure access

### Contact

In case of security incidents:

1. Notify the system administrator
2. Provide relevant logs
3. Document the incident

### Incident Workflow (Diagram)

```mermaid
%%{ init: { "theme": "default" } }%%
flowchart TD
    A[Incident Detected] --> B[Isolate System]
    B --> C[Secure Logs]
    C --> D[Revoke Access]
    D --> E[Analyze Root Cause]
    E --> F[Assess Impact]
    F --> G[Document Incident]
    G --> H[Clean System]
    H --> I[Apply Updates & Fixes]
    I --> J[Reactivate System]
```