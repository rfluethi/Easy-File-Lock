# Technische Dokumentation

## 1. Systemarchitektur

### 1.1 Verzeichnisstruktur
```
secure-files/                  # Außerhalb des WebRoots (nicht erreichbar)
├── config/
│   └── secure-config.php      # Konfigurationsdatei
├── logs/
│   └── access.log            # Log-Datei
├── group-1/
│   ├── example-1.pdf         # Beispiel für Subscriber
│   └── [weitere Dateien für Subscriber]
└── group-2/
    ├── example-2.pdf         # Beispiel für Contributor
    └── [weitere Dateien für Contributor]
```

### 1.2 Datei-Lade-Reihenfolge
1. `check-access.php` wird aufgerufen
2. `secure-config.php` wird geladen (MUSS zuerst geladen werden!)
3. Konstanten werden geprüft (z.B. `MIN_MEMORY_LIMIT`)
4. WordPress wird geladen
5. Zugriffskontrolle wird durchgeführt
6. Datei wird bereitgestellt

### 1.3 Berechtigungen
- `secure-files/`: 755 (drwxr-xr-x)
- `secure-files/config/`: 755 (drwxr-xr-x)
- `secure-files/logs/`: 755 (drwxr-xr-x)
- `secure-files/logs/access.log`: 644 (-rw-r--r--)
- `secure-files/group-*/*`: 644 (-rw-r--r--)

### 1.4 Log-Rotation
- Log-Dateien werden bei 5 MB Größe rotiert
- Alte Log-Dateien werden mit Zeitstempel umbenannt
- Maximal 5 Log-Dateien werden behalten
- Älteste Log-Datei wird automatisch gelöscht

### 1.5 Beispiel-URLs
- Subscriber-Zugriff: `/protected/group-1/example-1.pdf`
- Contributor-Zugriff: `/protected/group-2/example-2.pdf`

## 2. Konfiguration

### 2.1 WordPress-Pfad
```php
// Wenn WordPress in einem Unterverzeichnis liegt
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');

// Wenn WordPress direkt im WebRoot liegt
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');
```

### 2.2 Rollenzuordnung
```php
$role_folders = [
    'subscriber' => 'group-1',    // Zugriff auf /group-1/
    'contributor' => 'group-2'    // Zugriff auf /group-2/
];
```

### 2.3 Sicherheitseinstellungen
```php
// Cache-Control
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Sicherheits-Header
define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\'',
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'
]);
```

## 3. Fehlerbehebung

### 3.1 Log-Datei prüfen
```bash
tail -f secure-files/logs/access.log
```

### 3.2 Debug-Modus
```php
define('DEBUG_MODE', true);
```

### 3.3 Häufige Fehler
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

## 4. Performance

### 4.1 Download-Optimierung
- Direkte Downloads bis 1 MB
- Chunked Downloads für größere Dateien
- Chunk-Größe: 4 MB
- Kein Caching für geschützte Dateien

### 4.2 Logging
- Debug-Modus für Entwicklung
- Detaillierte Log-Einträge
- Automatische Log-Rotation
- Fehlerbehandlung

## 5. Wartung

### 5.1 Regelmäßige Aufgaben
- Log-Dateien überprüfen
- Berechtigungen prüfen
- WordPress-Integration testen
- Sicherheits-Header validieren

### 5.2 Backup
- Regelmäßige Backups von `secure-files/`
- Backup der Log-Dateien
- Backup der Konfiguration

### 5.3 Updates
- WordPress aktualisieren
- PHP-Version prüfen
- Sicherheits-Header aktualisieren
- MIME-Types überprüfen 