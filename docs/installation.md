# Installation

## Was ist das WebRoot-Verzeichnis?

Der WebRoot ist das Hauptverzeichnis auf einem Webserver, in dem z. B. eine WordPress-Installation liegen muss, damit sie im Internet erreichbar ist. Typische Namen sind *htdocs*, *www*, *html* oder *public_html*. Mit einem Browser kann nicht auf Dateien außerhalb dieses Verzeichnisses zugegriffen werden.

## Schnellstart Installation

1. Release-Dateien herunterladen:
   - [protected.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/secure-files.zip)

2. Dateien entpacken und kopieren:
   - `protected.zip` → Inhalt in das WebRoot-Verzeichnis kopieren
   - `secure-files.zip` → Inhalt außerhalb des WebRoots kopieren

3. WordPress konfigurieren:
   ```php
   // In wp-config.php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

### Detaillierte Anleitung

1. **Voraussetzungen**
   - WordPress 5.0 oder höher
   - PHP 7.4 oder höher
   - Mindestens 128M PHP Memory-Limit
   - Schreibrechte für Log-Verzeichnis

2. **Verzeichnisstruktur einrichten**
   ```bash
   # WebRoot-Verzeichnis
   /path/to/webroot/
   ├── wordpress/             # WordPress-Installation
   │   └── wp-load.php
   └── protected/             # Aus protected.zip
       ├── .htaccess
       └── check-access.php

   # Außerhalb des WebRoots
   /path/to/
   └── secure-files/          # Aus secure-files.zip
       ├── config/
       │   └── secure-config.php
       ├── logs/
       │   └── access.log
       ├── group-1/
       └── group-2/
   ```

3. **Berechtigungen setzen**
   ```bash
   # Verzeichnisse
   chmod 755 secure-files
   chmod 755 secure-files/config
   chmod 755 secure-files/logs
   chmod 755 secure-files/group-*

   # Dateien
   chmod 644 secure-files/config/secure-config.php
   chmod 644 secure-files/logs/access.log
   chmod 644 secure-files/group-*/*
   ```

4. **Konfiguration anpassen**

   a. WordPress-Konfiguration (`wp-config.php`):
   ```php
   // Pfad zum geschützten Dateiverzeichnis
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

   b. Secure-Config (`secure-files/config/secure-config.php`):
   ```php
   // WordPress-Pfad (flexibel)
   define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');  // Wenn WordPress in /wordpress/ liegt
   // Alternative: define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');  // Wenn WordPress direkt im WebRoot liegt

   // Logging-Konfiguration
   define('LOG_DIR', dirname(__DIR__) . '/logs');
   define('LOG_FILE', LOG_DIR . '/access.log');

   // Rollen und ihre zugehörigen Ordner
   $role_folders = [
       'subscriber' => 'group-1',    // Zugriff auf /group-1/
       'contributor' => 'group-2'    // Zugriff auf /group-2/
   ];

   // Download-Einstellungen
   define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
   define('CHUNK_SIZE', 4194304);               // 4 MB
   define('MIN_MEMORY_LIMIT', '128M');          // Minimale PHP Memory-Limit
   ```

5. **Testen**
   - WordPress-Benutzer anmelden
   - Datei über `/protected/group-1/example-1.pdf` aufrufen
   - Log-Datei auf Fehler prüfen

### Fehlerbehebung

1. **"Undefined constant MIN_MEMORY_LIMIT"**
   - Überprüfe, ob `secure-config.php` vor der Verwendung geladen wird
   - Überprüfe den Pfad zu `secure-config.php`
   - Stelle sicher, dass die Datei existiert und lesbar ist

2. **"WordPress nicht gefunden"**
   - Überprüfe den Pfad in `secure-config.php`
   - Stelle sicher, dass `wp-load.php` existiert
   - Prüfe die Berechtigungen

3. **"Konfigurationsdatei nicht gefunden"**
   - Überprüfe die Verzeichnisstruktur
   - Stelle sicher, dass `secure-files` außerhalb des WebRoots liegt
   - Prüfe die Berechtigungen

4. **Log-Datei prüfen**
   ```bash
   tail -f secure-files/logs/access.log
   ```

### Sicherheitshinweise

1. **Verzeichnisstruktur**
   - `secure-files/` MUSS außerhalb des WebRoots liegen
   - Nur `protected/` ist von außen erreichbar

2. **Berechtigungen**
   - Minimale Berechtigungen verwenden
   - Regelmäßig prüfen

3. **Debug-Modus**
   - Nur für Entwicklung aktivieren
   - In Produktion deaktivieren

4. **Updates**
   - WordPress aktuell halten
   - PHP-Version prüfen
   - Sicherheits-Header validieren

## Verzeichnisstruktur

### Repository-Struktur
```
Website-Access-Control-Basic/
├── protected/                 # Wird in das WebRoot-Verzeichnis kopiert
│   ├── .htaccess              # URL-Weiterleitung
│   └── check-access.php       # Zugriffskontrolle
├── secure-files/              # Wird außerhalb des WebRoots kopiert
│   ├── config/
│   │   └── secure-config.php  # Konfigurationsdatei
│   ├── logs/
│   │   └── access.log        # Log-Datei
│   ├── group-1/
│   │   └── example-1.pdf      # Beispiel für Subscriber
│   └── group-2/
│       └── example-2.pdf      # Beispiel für Contributor
└── docs/                      # Dokumentation
```

### Beispiel-Installations-Struktur
Nach der Installation könnte Ihre Verzeichnisstruktur so aussehen:

```
/var/www/                      # Server-Root
├── wordpress/                # WordPress-Installation
│   └── wp-load.php
├── protected/                # Kopiert aus protected.zip
│   ├── .htaccess            # URL-Weiterleitung
│   └── check-access.php     # Zugriffskontrolle
└── secure-files/            # Außerhalb des WebRoots (nicht erreichbar)
    ├── config/
    │   └── secure-config.php # Konfigurationsdatei
    ├── logs/
    │   └── access.log       # Log-Datei
    ├── group-1/
    │   └── example-1.pdf     # Beispiel für Subscriber
    └── group-2/
        └── example-2.pdf     # Beispiel für Contributor
```

**Hinweis:**  
- Die WordPress-Installation kann direkt im WebRoot oder in einem Unterverzeichnis liegen
- Wichtig ist nur, dass `secure-files` **außerhalb** des WebRoots liegt

## Systemvoraussetzungen

### Server-Anforderungen
- Apache 2.4 oder höher
- mod_rewrite aktiviert
- PHP 7.4 oder höher
- Ausreichend Speicherplatz für geschützte Dateien
- Schreibrechte für das Log-Verzeichnis

### PHP-Extensionen
- fileinfo (für MIME-Type-Erkennung)
- mbstring (für String-Operationen)
- openssl (für sichere Verbindungen)

### WordPress
- Version 5.0 oder höher
- Aktivierte Benutzerrollen
- Schreibrechte im WordPress-Verzeichnis

## Installation

### 1. Dateien entpacken und kopieren

1. Entpacke beide ZIP-Archive:
   - `protected.zip` → Inhalt in das WebRoot-Verzeichnis kopieren
   - `secure-files.zip`