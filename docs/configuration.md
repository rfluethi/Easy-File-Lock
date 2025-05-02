# Konfiguration des Dateitresors

In diesem Abschnitt erfährst du, wie du den Dateitresor für deine WordPress-Installation korrekt konfigurierst.

## Grundlegende Konfiguration

### 1. Rollen-Konfiguration

Definiere in `secure-config.php` die Zuordnung von WordPress-Rollen zu Ordnern:

```php
$role_folders = [
    'seminar-website-basis' => 's-wsb',
    'cv-interessent'        => 'secure-docs'
];
```

### 2. Erlaubte Dateitypen

Konfiguriere die erlaubten MIME-Types:

```php
$allowed_mime_types = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'html' => 'text/html',
    'htm'  => 'text/html',
    'txt'  => 'text/plain',
    'csv'  => 'text/csv'
];
```

## Beispiel-Konfigurationen

### 1. Basis-Konfiguration
```php
// Debug-Modus deaktivieren
define('DEBUG_MODE', false);

// Standard-Chunk-Größe
define('CHUNK_SIZE', 4194304); // 4 MB

// Maximale Download-Größe
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576); // 1 MB
```

### 2. Performance-Optimierung
```php
// Erhöhte Chunk-Größe für schnelle Downloads
define('CHUNK_SIZE', 8388608); // 8 MB

// Cache-Header für statische Dateien
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600); // 1 Stunde
```

### 3. Sicherheits-Konfiguration
```php
// Strikte MIME-Type-Validierung
define('STRICT_MIME_CHECK', true);

// Zusätzliche Sicherheitsheader
define('ADD_SECURITY_HEADERS', true);

// Logging aktivieren
define('ENABLE_LOGGING', true);
```

## Performance-Tuning

### 1. Chunk-Größe optimieren

Die Chunk-Größe beeinflusst die Download-Geschwindigkeit:

```php
// Kleine Dateien (bis 1 MB)
define('CHUNK_SIZE', 1048576); // 1 MB

// Mittlere Dateien (1-10 MB)
define('CHUNK_SIZE', 4194304); // 4 MB

// Große Dateien (10+ MB)
define('CHUNK_SIZE', 8388608); // 8 MB
```

### 2. Caching konfigurieren

Für statische Dateien kann Caching aktiviert werden:

```php
// Cache aktivieren
define('ENABLE_CACHE', true);

// Cache-Dauer in Sekunden
define('CACHE_DURATION', 3600); // 1 Stunde

// Cache-Header
header('Cache-Control: public, max-age=' . CACHE_DURATION);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + CACHE_DURATION) . ' GMT');
```

### 3. Ressourcen-Optimierung

```php
// PHP-Speicherlimit anpassen
ini_set('memory_limit', '256M');

// Maximale Ausführungszeit
set_time_limit(300); // 5 Minuten

// Output-Buffering
ob_start();
```

## Debugging-Optionen

### 1. Debug-Modus

```php
// Debug-Modus aktivieren
define('DEBUG_MODE', true);

// Debug-Logging
if (DEBUG_MODE) {
    error_log('Datei: ' . $requested_file);
    error_log('Benutzer: ' . $user->user_login);
    error_log('Rolle: ' . $role);
}
```

### 2. Fehlerprotokollierung

```php
// Detaillierte Fehlerprotokolle
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR

// Log-Funktion
function log_message($level, $message) {
    if (LOG_LEVEL === 'DEBUG' || $level === 'ERROR') {
        error_log("[$level] $message");
    }
}
```

### 3. Performance-Monitoring

```php
// Performance-Metriken
define('ENABLE_PERFORMANCE_MONITORING', true);

// Timing-Funktion
function log_performance($operation, $start_time) {
    if (ENABLE_PERFORMANCE_MONITORING) {
        $duration = microtime(true) - $start_time;
        error_log("Performance: $operation took {$duration}s");
    }
}
```

## Best Practices

### 1. Sicherheit
- Aktiviere `STRICT_MIME_CHECK`
- Setze `ADD_SECURITY_HEADERS`
- Aktiviere `ENABLE_LOGGING`

### 2. Performance
- Optimiere `CHUNK_SIZE`
- Aktiviere Caching für statische Dateien
- Überwache Performance-Metriken

### 3. Wartung
- Regelmäßige Log-Analyse
- Performance-Monitoring
- Sicherheits-Updates

Mit diesen Schritten ist die Konfiguration abgeschlossen. Weiter geht es mit der Installation! 