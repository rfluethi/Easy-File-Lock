# Konfigurationsdokumentation

## 1. Wichtige Hinweise

### 1.1 Lade-Reihenfolge
Die Reihenfolge der Einbindungen ist **kritisch**:
1. `secure-config.php` MUSS zuerst geladen werden
2. Erst dann dürfen Konstanten wie `MIN_MEMORY_LIMIT` verwendet werden
3. WordPress wird erst danach geladen

### 1.2 Fehlerbehandlung
```php
// In check-access.php
$config_file = dirname(dirname(__DIR__)) . '/secure-files/config/secure-config.php';
if (!file_exists($config_file)) {
    die('Fehler: Konfigurationsdatei nicht gefunden. Bitte Installation überprüfen.');
}
require_once $config_file;

// Jetzt erst WordPress laden
if (!file_exists(WP_CORE_PATH)) {
    die('Fehler: WordPress nicht gefunden. Bitte Pfad in secure-config.php überprüfen.');
}
require_once WP_CORE_PATH;
```

## 2. WordPress-Pfad

### 2.1 Standard-Konfiguration
```php
// Wenn WordPress in einem Unterverzeichnis liegt
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');

// Wenn WordPress direkt im WebRoot liegt
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');
```

### 2.2 Verzeichnisstruktur
```
/path/to/
├── wordpress/             # WordPress-Installation
│   └── wp-load.php
├── protected/            # Im WebRoot
│   ├── .htaccess
│   └── check-access.php
└── secure-files/        # Außerhalb des WebRoots
    ├── config/
    │   └── secure-config.php
    ├── logs/
    │   └── access.log
    ├── group-1/
    │   └── example-1.pdf
    └── group-2/
        └── example-2.pdf
```

## 3. Konfigurationsoptionen

### 3.1 Rollenzuordnung
```php
$role_folders = [
    'subscriber' => 'group-1',    // Zugriff auf /group-1/
    'contributor' => 'group-2'    // Zugriff auf /group-2/
];
```

### 3.2 Download-Einstellungen
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimale PHP Memory-Limit
```

### 3.3 Logging
```php
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('LOG_MAX_FILES', 5);               // Max. 5 Log-Dateien
```

### 3.4 Sicherheit
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

## 4. Debug-Modus

### 4.1 Aktivierung
```php
define('DEBUG_MODE', true);
```

### 4.2 Log-Datei prüfen
```bash
tail -f secure-files/logs/access.log
```

### 4.3 Häufige Fehler
1. **"Undefined constant MIN_MEMORY_LIMIT"**
   - Konfigurationsdatei wird nicht geladen
   - Pfad zur Konfigurationsdatei ist falsch

2. **"WordPress nicht gefunden"**
   - Pfad zu `wp-load.php` ist falsch
   - WordPress-Installation fehlt

3. **"Konfigurationsdatei nicht gefunden"**
   - Verzeichnisstruktur ist falsch
   - Berechtigungen sind falsch

4. **"Zugriff verweigert"**
   - Benutzer ist nicht eingeloggt
   - Benutzer hat nicht die richtige Rolle
   - Datei liegt im falschen Ordner 