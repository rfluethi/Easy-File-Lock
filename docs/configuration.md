# Konfigurationsdokumentation

## 1. WordPress-Pfad

### 1.1 Standard-Konfiguration
```php
// Wenn WordPress in einem Unterverzeichnis des WebRoots liegt (z.B. /main/)
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/main/wp-load.php');

// Wenn WordPress direkt im WebRoot liegt
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wp-load.php');
```

### 1.2 Pfadberechnung
- `dirname(__DIR__, 2)` geht zwei Verzeichnisse hoch
- Bei WordPress in einem Unterverzeichnis:
  - Start: `/path/to/secure-files/config/`
  - Nach `dirname(__DIR__, 2)`: `/path/to/`
  - Final: `/path/to/main/wp-load.php`

**Hinweis:**  
- Der Pfad muss zum tatsächlichen Standort von WordPress passen
- Der Name des WebRoot-Verzeichnisses spielt keine Rolle
- Wichtig ist nur der relative Pfad von `secure-files` zu WordPress

## 2. Rollenzuordnung

### 2.1 Standard-Konfiguration
```php
$role_folders = [
    'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
    'contributor' => 'group-2'    // Zugriff auf example-2.pdf
];
```

### 2.2 Eigene Rollen hinzufügen
```php
$role_folders = [
    'subscriber' => 'group-1',
    'contributor' => 'group-2',
    'editor' => 'group-3',        // Neue Rolle
    'author' => 'group-4'         // Neue Rolle
];
```

## 3. Download-Einstellungen

### 3.1 Standard-Konfiguration
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 524288);  // 512 KB
define('CHUNK_SIZE', 1048576);              // 1 MB
```

### 3.2 Anpassung der Chunk-Größe
```php
// Für schnellere Downloads
define('CHUNK_SIZE', 4194304);  // 4 MB

// Für langsamere Verbindungen
define('CHUNK_SIZE', 524288);   // 512 KB
```

## 4. Debug-Modus

### 4.1 Aktivierung
```php
define('DEBUG_MODE', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 4.2 Logging
```php
error_log('Debug: ' . $message);
error_log('Chunk: ' . $chunk_number . ' von ' . $total_chunks);
```

## 5. Sicherheit

### 5.1 Cache-Header
```php
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
```

### 5.2 MIME-Type-Validierung
```php
$allowed_mimes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
```

## 6. Fehlerbehebung

### 6.1 Pfadprobleme
- Überprüfe den WordPress-Pfad in `secure-config.php`
- Stelle sicher, dass die Verzeichnisstruktur korrekt ist
- Prüfe die Berechtigungen der Verzeichnisse

### 6.2 Zugriffsprobleme
- Überprüfe die Rollenzuordnung
- Stelle sicher, dass die Benutzer die richtigen Rollen haben
- Prüfe die WordPress-Authentifizierung

### 6.3 Download-Probleme
- Überprüfe die Chunk-Größe
- Stelle sicher, dass genügend Arbeitsspeicher verfügbar ist
- Prüfe die PHP-Konfiguration (memory_limit, max_execution_time)

## 2. Logging-Konfiguration

### 2.1 Standard-Konfiguration
```php
// Logging-Verzeichnis und Datei
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false);
```

### 2.2 Logging-Funktion
```php
function secure_log($message) {
    if (DEBUG_MODE) {
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, LOG_FILE);
    }
}
```

### 2.3 Log-Inhalte
- Benutzerrollen und Zugriffsversuche
- Dateiübertragungen und Chunk-Übertragungen
- Fehler und Warnungen
- Performance-Metriken

Mit diesen Schritten ist die Konfiguration abgeschlossen. Weiter geht es mit der Installation! 