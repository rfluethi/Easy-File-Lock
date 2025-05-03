# Installation

## Überblick

Diese Anleitung beschreibt Schritt für Schritt die Installation und Einrichtung eines Systems zur rollenbasierten Dateifreigabe mit WordPress.  
Sie richtet sich an technisch versierte Anwender mit Zugriff auf das Server-Dateisystem und Grundkenntnissen in WordPress und PHP.

## Schnellstart

1. **ZIP-Dateien herunterladen**

   - `protected.zip`
   - `secure-files.zip`

2. **Dateien entpacken und kopieren**

   - Inhalt von `protected.zip` in das **WebRoot-Verzeichnis** kopieren
   - Inhalt von `secure-files.zip` in ein Verzeichnis **außerhalb des WebRoots** ablegen

   **Was ist das WebRoot-Verzeichnis?**  
   Das WebRoot ist das öffentlich erreichbare Hauptverzeichnis des Webservers – z. B. `htdocs`, `www`, `html` oder `public_html`. Dateien außerhalb dieses Verzeichnisses sind nicht direkt über den Browser erreichbar.

3. **WordPress-Konfiguration anpassen**

   In der Datei `wp-config.php` folgende Zeile ergänzen:

   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

## Voraussetzungen

**Systemanforderungen:**

- Apache 2.4 oder höher mit aktiviertem `mod_rewrite`
- PHP 7.4 oder höher
- mindestens 128 MB PHP Memory-Limit
- Schreibrechte für das Log-Verzeichnis
- ausreichend Speicherplatz für geschützte Dateien

**PHP-Extensions:**

- `fileinfo`
- `mbstring`
- `openssl`

**WordPress:**

- Version 5.0 oder höher
- Benutzerrollen aktiviert
- Schreibrechte im WordPress-Verzeichnis

## Verzeichnisstruktur auf dem Server

Nach der Installation sollte die Struktur wie folgt aussehen:

```txt
/var/www/
├── html/                      ← WebRoot (öffentlich erreichbar)
│   ├── wordpress/             ← WordPress-Installation
│   │   └── wp-load.php
│   └── protected/             ← aus protected.zip, im WebRoot
│       ├── .htaccess
│       └── check-access.php
└── secure-files/              ← aus secure-files.zip, außerhalb des WebRoots
    ├── config/
    │   └── secure-config.php
    ├── logs/
    │   └── access.log
    ├── group-1/
    │   └── example-1.pdf
    └── group-2/
        └── example-2.pdf
```

## Dateiberechtigungen setzen

**Verzeichnisse:**

- `secure-files`, `config`, `logs`, `group-*`: Lese- und Ausführungsrechte (z. B. `755`)

**Dateien:**

- `secure-config.php`, `access.log`, Dateien in `group-*`: Leserechte (z. B. `644`)

## Konfiguration

### a) WordPress-Konfiguration (`wp-config.php`)

```php
define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
```

### b) Secure-Konfiguration (`secure-files/config/secure-config.php`)

```php
// Pfad zur WordPress-Installation
define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wordpress/wp-load.php');
// Alternativ, wenn WordPress direkt im WebRoot liegt:
// define('WP_CORE_PATH', dirname(dirname(__DIR__)) . '/wp-load.php');

// Log-Verzeichnis und Log-Datei
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');

// Rollen-Zuordnung
$role_folders = [
    'subscriber'  => 'group-1',
    'contributor' => 'group-2'
];

// Download-Einstellungen
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576); // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MIN_MEMORY_LIMIT', '128M');
```

## Installation testen

1. In WordPress als registrierter Benutzer anmelden
2. Geschützte Datei im Browser öffnen, z. B.:
   `https://deine-domain.de/protected/group-1/example-1.pdf`
3. Prüfen, ob ein Zugriff protokolliert wurde:
   Die Log-Datei befindet sich unter `secure-files/logs/access.log` und ist per FTP zugänglich.

## Fehlerbehebung

**"Undefined constant MIN_MEMORY_LIMIT"**

- Wird `secure-config.php` korrekt eingebunden?
- Ist der Pfad zur Datei korrekt angegeben?
- Ist die Datei vorhanden und lesbar?

**"WordPress nicht gefunden"**

- Pfadangabe in `secure-config.php` prüfen
- Existiert `wp-load.php` am angegebenen Ort?
- Berechtigungen korrekt gesetzt?

**"Konfigurationsdatei nicht gefunden"**

- Liegt `secure-files` außerhalb des WebRoots?
- Ist die Verzeichnisstruktur korrekt eingerichtet?
- Leserechte vorhanden?

**Zur Fehleranalyse sollte die Datei `secure-files/logs/access.log` geprüft werden.** Sie ist über FTP zugänglich und enthält Informationen über Zugriffsversuche.

**Weitere mögliche Fehler** findest du in der Datei **[Fehlerbehebung](troubleshooting.md)**.

## Sicherheitshinweise

- **Verzeichnisstruktur:**
  Das Verzeichnis `secure-files` muss außerhalb des WebRoots liegen.
  Nur das Verzeichnis `protected` darf öffentlich erreichbar sein.

- **Dateiberechtigungen:**
  Verwende minimale Rechte. Berechtigungen regelmäßig überprüfen.

- **Debug-Modus:**
  Nur in Entwicklungsumgebungen aktivieren. In Produktivsystemen deaktivieren.

- **Systempflege:**
  WordPress und PHP aktuell halten. Serverkonfiguration und Sicherheits-Header regelmäßig prüfen.

**Weitere sicherheitsrelevante Hinweise** findest du in der Datei **[Sicherheitsrichtlinien](security.md)**.
