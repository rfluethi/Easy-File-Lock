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

**Hinweis:**  
- Die Dateien aus `protected/` werden in das WebRoot-Verzeichnis kopiert
- `secure-files/` muss außerhalb des WebRoots liegen
- Der Name des WebRoot-Verzeichnisses kann variieren (`public_html`, `htdocs`, `www`, etc.)

### 1.2 Berechtigungen
- `secure-files/`: 755 (drwxr-xr-x)
- `secure-files/config/`: 755 (drwxr-xr-x)
- `secure-files/logs/`: 755 (drwxr-xr-x)
- `secure-files/logs/access.log`: 644 (-rw-r--r--)
- `secure-files/group-*/*`: 644 (-rw-r--r--)

### 1.3 Log-Rotation
- Log-Dateien werden bei 5 MB Größe rotiert
- Alte Log-Dateien werden mit Zeitstempel umbenannt
- Maximal 5 Log-Dateien werden behalten
- Älteste Log-Datei wird automatisch gelöscht

### 1.4 Dateiübertragung
- Direkte Downloads bis 1 MB
- Chunked Downloads für größere Dateien (4 MB Chunks)
- Optimierte Pufferung und Flush-Mechanismen
- Fortschrittsüberwachung im Debug-Modus

### 1.5 Beispiel-URLs
- Subscriber-Zugriff: `/protected/group-1/example-1.pdf`
- Contributor-Zugriff: `/protected/group-2/example-2.pdf`

## 2. Konfiguration

### 2.1 WordPress-Pfad
```php
// Standard-Konfiguration (WordPress direkt im WebRoot)
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wp-load.php');

// Alternative (WordPress in Unterverzeichnis)
define('WP_CORE_PATH', dirname(__DIR__, 2) . '/main/wp-load.php');
```

### 2.2 Rollenzuordnung
```php
$role_folders = [
    'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
    'contributor' => 'group-2'    // Zugriff auf example-2.pdf
];
```

### 2.3 Download-Einstellungen
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1073741824);         // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimale PHP Memory-Limit
```

## 3. Sicherheit

### 3.1 Zugriffskontrolle
- Nur eingeloggte WordPress-Benutzer
- Rollenbasierte Zugriffsrechte
- Administratoren haben Zugriff auf alle Dateien
- Andere Rollen nur auf ihre zugewiesenen Verzeichnisse

### 3.2 Datei-Validierung
- Pfad-Traversal-Schutz
- MIME-Type-Validierung
- Maximale Dateigröße: 1 GB
- Erlaubte Dateitypen in `secure-config.php`
- Dateinamen-Validierung (nur alphanumerisch, Punkt, Unterstrich, Bindestrich)

### 3.3 Sicherheits-Header
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Content-Security-Policy: default-src 'self'
- Strict-Transport-Security: max-age=31536000; includeSubDomains

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

## 5. Fehlerbehebung

### 5.1 Häufige Probleme
1. **403 Forbidden**
   - Benutzer nicht eingeloggt
   - Falsche Benutzerrolle
   - Falscher Dateipfad

2. **404 Not Found**
   - Datei existiert nicht
   - Falscher Dateipfad
   - Berechtigungsprobleme

3. **413 Request Entity Too Large**
   - Datei größer als 1 GB
   - Server-Limits erreicht

4. **500 Internal Server Error**
   - PHP-Version < 7.4
   - PHP Memory-Limit < 128M
   - Falsche Berechtigungen
   - Fehlende WordPress-Integration

### 5.2 Log-Analyse
- Log-Datei: `secure-files/logs/access.log`
- Format: `[YYYY-MM-DD HH:MM:SS] Nachricht`
- Debug-Modus für detaillierte Logs
- Log-Rotation bei 5 MB

### 5.3 Berechtigungsprüfung
```bash
# Verzeichnisberechtigungen prüfen
ls -la secure-files/
ls -la secure-files/config/
ls -la secure-files/logs/
ls -la secure-files/group-*/

# Log-Datei prüfen
tail -f secure-files/logs/access.log
```

## 6. Wartung

### 6.1 Regelmäßige Aufgaben
- Log-Dateien überprüfen
- Berechtigungen prüfen
- WordPress-Integration testen
- Sicherheits-Header validieren

### 6.2 Backup
- Regelmäßige Backups von `secure-files/`
- Backup der Log-Dateien
- Backup der Konfiguration

### 6.3 Updates
- WordPress aktualisieren
- PHP-Version prüfen
- Sicherheits-Header aktualisieren
- MIME-Types überprüfen 