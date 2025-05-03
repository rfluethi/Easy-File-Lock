# Konfigurationsdokumentation

## Wichtige Hinweise

### Lade-Reihenfolge
Die Reihenfolge der Einbindungen ist **kritisch**:
1. `secure-config.php` MUSS zuerst geladen werden
2. Erst dann dürfen Konstanten wie `MIN_MEMORY_LIMIT` verwendet werden
3. WordPress wird erst danach geladen

### Fehlerbehandlung
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

## Download-Einstellungen

Mit den folgenden Einstellungen kannst du das Verhalten beim Dateidownload steuern:

```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimales PHP Memory-Limit
```

**Erklärung:**
- **MAX_DIRECT_DOWNLOAD_SIZE:** Dateien bis zu dieser Größe werden direkt (ohne Chunking) an den Client gesendet. Größere Dateien werden gestreamt.
- **CHUNK_SIZE:** Größe der Datenblöcke beim gestreamten Download. Zu kleine Werte verlangsamen, zu große Werte können Speicherprobleme verursachen.
- **MAX_FILE_SIZE:** Maximale Dateigröße, die überhaupt ausgeliefert wird. Alles darüber wird abgelehnt.
- **MIN_MEMORY_LIMIT:** Das Skript prüft, ob das PHP-Memory-Limit mindestens diesen Wert hat.

**Beispiel:**
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('CHUNK_SIZE', 8 * 1024 * 1024);              // 8 MB
define('MAX_FILE_SIZE', 500 * 1024 * 1024);         // 500 MB
define('MIN_MEMORY_LIMIT', '256M');
```

## Logging-Einstellungen

Das Logging dient der Nachvollziehbarkeit und Fehleranalyse. Die wichtigsten Einstellungen:

```php
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('LOG_MAX_FILES', 5);               // Max. 5 Log-Dateien
define('DEBUG_MODE', true);               // Debug-Ausgaben aktivieren
```

**Erklärung:**
- **LOG_DIR:** Verzeichnis, in dem die Log-Dateien abgelegt werden.
- **LOG_FILE:** Name der Log-Datei.
- **LOG_MAX_SIZE:** Ab dieser Größe wird die Log-Datei rotiert (umbenannt und neu begonnen).
- **LOG_MAX_FILES:** Wie viele alte Log-Dateien maximal behalten werden.
- **DEBUG_MODE:** Wenn aktiviert, werden zusätzliche Debug-Informationen ins Log geschrieben.

**Beispiel:**
```php
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10 MB
define('LOG_MAX_FILES', 10);
define('DEBUG_MODE', false);              // Debug-Logging deaktivieren (empfohlen in Produktion)
```

## Sicherheitseinstellungen

Hier werden die wichtigsten Sicherheitsaspekte konfiguriert:

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

**Erklärung:**
- **CACHE_CONTROL:** Steuert, wie Browser und Proxies die ausgelieferten Dateien cachen dürfen. Standard: kein Caching.
- **SECURITY_HEADERS:** Array mit HTTP-Headern, die für jede Datei gesetzt werden. Sie schützen vor typischen Web-Angriffen (z. B. XSS, Clickjacking).

**Beispiel:**
```php
define('CACHE_CONTROL', 'no-store, no-cache, must-revalidate, max-age=0');
define('SECURITY_HEADERS', [
    'X-Frame-Options: SAMEORIGIN',
    'X-Content-Type-Options: nosniff',
    'Referrer-Policy: no-referrer'
]);
```

## Rollenzuordnung (Ordner-Mapping)

Hier legst du fest, welche WordPress-Rolle auf welches Verzeichnis zugreifen darf:

```php
$role_folders = [
    'subscriber'  => 'group-1',
    'contributor' => 'group-1', // Beide auf das gleiche Verzeichnis
    'author'      => 'group-2'
];
```

**Beispiel:**
- Sowohl "subscriber" als auch "contributor" haben Zugriff auf `/secure-files/group-1/`.
- "author" hat Zugriff auf `/secure-files/group-2/`.

## MIME-Type Einstellungen

Erlaube nur bestimmte Dateitypen für den Download:

```php
$allowed_mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    // weitere Typen ...
];
```

**Beispiel:**
```php
$allowed_mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
```

## Beispiel für eine vollständige Konfiguration

```php
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);
define('LOG_MAX_FILES', 5);
define('MAX_DIRECT_DOWNLOAD_SIZE', 2 * 1024 * 1024);
define('CHUNK_SIZE', 8 * 1024 * 1024);
define('MAX_FILE_SIZE', 500 * 1024 * 1024);
define('MIN_MEMORY_LIMIT', '256M');
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');
define('DEBUG_MODE', false);

define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\'',
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'
]);

$role_folders = [
    'subscriber'  => 'group-1',
    'contributor' => 'group-1',
    'author'      => 'group-2'
];

$allowed_mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];
```

**Tipp:**  
Passe die Werte immer an deine Serverumgebung und Sicherheitsanforderungen an. In der Produktion sollte `DEBUG_MODE` immer auf `false` stehen!
