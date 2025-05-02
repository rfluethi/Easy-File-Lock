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

**Wichtige Hinweise:**
- Die Rollen-Namen müssen exakt mit den WordPress-Rollen übereinstimmen
- Die Ordner-Namen dürfen keine Leerzeichen oder Sonderzeichen enthalten
- Jede Rolle kann nur einem Ordner zugeordnet werden

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

**Sicherheitshinweise:**
- Füge nur MIME-Types hinzu, die wirklich benötigt werden
- Aktiviere `STRICT_MIME_CHECK` für zusätzliche Sicherheit
- Überprüfe regelmäßig die MIME-Type-Liste

## Erweiterte Konfiguration

### 1. Debug-Modus

```php
// Debug-Modus aktivieren/deaktivieren
define('DEBUG_MODE', false);
```

**Auswirkungen:**
- Aktiviert: Detaillierte Fehlermeldungen und Logging
- Deaktiviert: Nur kritische Fehler werden angezeigt
- Empfehlung: Im Produktivbetrieb deaktivieren

### 2. Chunk-Größe

```php
// Standard-Chunk-Größe (4 MB)
define('CHUNK_SIZE', 4194304);
```

**Auswirkungen:**
- Größere Chunks: Schnellere Downloads, höherer Speicherverbrauch
- Kleinere Chunks: Langsamere Downloads, geringerer Speicherverbrauch
- Empfehlung: An Server-Ressourcen anpassen

### 3. Maximale Download-Größe

```php
// Maximale direkte Download-Größe (1 MB)
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);
```

**Auswirkungen:**
- Dateien über dieser Größe werden immer gestreamt
- Verhindert Timeouts bei großen Dateien
- Empfehlung: An Server-Konfiguration anpassen

### 4. Caching

```php
// Cache aktivieren
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600); // 1 Stunde
```

**Auswirkungen:**
- Verbesserte Performance für statische Dateien
- Reduzierte Server-Last
- Empfehlung: Für statische Inhalte aktivieren

### 5. Sicherheitseinstellungen

```php
// Strikte MIME-Type-Validierung
define('STRICT_MIME_CHECK', true);

// Zusätzliche Sicherheitsheader
define('ADD_SECURITY_HEADERS', true);

// Logging aktivieren
define('ENABLE_LOGGING', true);
```

**Auswirkungen:**
- `STRICT_MIME_CHECK`: Verhindert MIME-Type-Spoofing
- `ADD_SECURITY_HEADERS`: Verbesserte Browser-Sicherheit
- `ENABLE_LOGGING`: Detaillierte Zugriffsprotokolle

## Performance-Optimierung

### 1. Chunk-Größe optimieren

Die optimale Chunk-Größe hängt von verschiedenen Faktoren ab:

```php
// Kleine Dateien (bis 1 MB)
define('CHUNK_SIZE', 1048576); // 1 MB

// Mittlere Dateien (1-10 MB)
define('CHUNK_SIZE', 4194304); // 4 MB

// Große Dateien (10+ MB)
define('CHUNK_SIZE', 8388608); // 8 MB
```

**Empfehlungen:**
- Teste verschiedene Chunk-Größen
- Überwache Server-Last
- Passe an typische Dateigrößen an

### 2. Caching-Strategien

```php
// Cache-Header für statische Dateien
header('Cache-Control: public, max-age=' . CACHE_DURATION);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + CACHE_DURATION) . ' GMT');
```

**Empfehlungen:**
- Lange Cache-Dauer für statische Inhalte
- Kurze Cache-Dauer für dynamische Inhalte
- Cache-Busting für Updates

## Best Practices

### 1. Sicherheit
- Aktiviere `STRICT_MIME_CHECK`
- Setze `ADD_SECURITY_HEADERS`
- Aktiviere `ENABLE_LOGGING`
- Regelmäßige Überprüfung der Logs

### 2. Performance
- Optimiere `CHUNK_SIZE`
- Aktiviere Caching für statische Dateien
- Überwache Performance-Metriken
- Regelmäßige Performance-Tests

### 3. Wartung
- Regelmäßige Log-Analyse
- Performance-Monitoring
- Sicherheits-Updates
- Backup-Strategie

Mit diesen Schritten ist die Konfiguration abgeschlossen. Weiter geht es mit der Installation! 