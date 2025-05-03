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
$config_file = dirname(__DIR__, 2) . '/secure-files/config/secure-config.php';
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
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/wp-load.php');

// Wenn WordPress direkt im WebRoot liegt
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wp-load.php');
```

### 2.2 Pfadberechnung
- `dirname(__DIR__, 2)` geht zwei Verzeichnisse hoch
- Bei WordPress in einem Unterverzeichnis:
  - Start: `/path/to/secure-files/config/`
  - Nach `dirname(__DIR__, 2)`: `/path/to/`
  - Final: `/path/to/wordpress/wp-load.php`

**Hinweis:**  
- Der Pfad muss zum tatsächlichen Standort von WordPress passen
- Der Name des WebRoot-Verzeichnisses spielt keine Rolle
- Wichtig ist nur der relative Pfad von `secure-files` zu WordPress

## 3. Rollenzuordnung

### 3.1 Standard-Konfiguration
```php
$role_folders = [
    'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
    'contributor' => 'group-2'    // Zugriff auf example-2.pdf
];
```

### 3.2 Eigene Rollen hinzufügen
```php
$role_folders = [
    'subscriber' => 'group-1',
    'contributor' => 'group-2',
    'editor' => 'group-3',        // Neue Rolle
    'author' => 'group-4'         // Neue Rolle
];
```

## 4. Download-Einstellungen

### 4.1 Standard-Konfiguration
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 524288);  // 512 KB
define('CHUNK_SIZE', 1048576);              // 1 MB
```

### 4.2 Anpassung der Chunk-Größe
```php
// Für schnellere Downloads
define('CHUNK_SIZE', 4194304);  // 4 MB

// Für langsamere Verbindungen
define('CHUNK_SIZE', 524288);   // 512 KB
```

## 5. Debug-Modus

### 5.1 Aktivierung
```php
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 5.2 Logging
```php
error_log('Debug: ' . $message);
error_log('Chunk: ' . $chunk_number . ' von ' . $total_chunks);
```

## 6. Sicherheit

### 6.1 Cache-Header
```php
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
```

### 6.2 MIME-Type-Validierung
```php
$allowed_mimes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
```

## 7. Fehlerbehebung

### 7.1 Pfadprobleme
- Überprüfe den WordPress-Pfad in `secure-config.php`
- Stelle sicher, dass die Verzeichnisstruktur korrekt ist
- Prüfe die Berechtigungen der Verzeichnisse

### 7.2 Zugriffsprobleme
- Überprüfe die Rollenzuordnung
- Stelle sicher, dass die Benutzer die richtigen Rollen haben
- Prüfe die WordPress-Authentifizierung

### 7.3 Download-Probleme
- Überprüfe die Chunk-Größe
- Stelle sicher, dass genügend Arbeitsspeicher verfügbar ist
- Prüfe die PHP-Konfiguration (memory_limit, max_execution_time)

## 8. Logging-Konfiguration

### 8.1 Standard-Konfiguration
```php
// Logging-Verzeichnis und Datei
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false);
```

### 8.2 Logging-Funktion
```php
function secure_log($message) {
    if (DEBUG_MODE) {
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, LOG_FILE);
    }
}
```

### 8.3 Log-Inhalte
- Benutzerrollen und Zugriffsversuche
- Dateiübertragungen und Chunk-Übertragungen
- Fehler und Warnungen
- Performance-Metriken

Mit diesen Schritten ist die Konfiguration abgeschlossen. Weiter geht es mit der Installation! 