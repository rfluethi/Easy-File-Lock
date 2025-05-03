# Installation des Dateitresors

## Schnellstart Installation

1. Repository klonen oder Release-Dateien herunterladen:
   ```bash
   git clone https://github.com/your-username/Website-Access-Control-Basic.git
   ```
   oder
   - [protected.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/secure-files.zip)

2. Dateien hochladen:
   - `protected/` in `public_html/` kopieren
   - `secure-files/` außerhalb des WebRoots anlegen

3. WordPress konfigurieren:
   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

## Verzeichnisstruktur

### Repository-Struktur
```
Website-Access-Control-Basic/
├── protected/           # Wird in public_html/ kopiert
│   ├── .htaccess       # URL-Weiterleitung
│   └── check-access.php # Zugriffskontrolle
├── secure-files/        # Wird außerhalb des WebRoots kopiert
│   ├── config/
│   │   └── secure-config.php  # Konfigurationsdatei
│   ├── group-1/
│   │   └── example-1.pdf      # Beispiel für Subscriber
│   └── group-2/
│       └── example-2.pdf      # Beispiel für Contributor
└── docs/               # Dokumentation
```

### Installations-Struktur
```
/var/www/
├── public_html/        # WebRoot
│   ├── wordpress/     # WordPress-Installation
│   └── protected/     # Kopiert aus protected/
│       ├── .htaccess  # URL-Weiterleitung
│       └── check-access.php # Zugriffskontrolle
└── secure-files/      # Kopiert aus secure-files/
    ├── config/
    │   └── secure-config.php  # Konfigurationsdatei
    ├── group-1/
    │   └── example-1.pdf      # Beispiel für Subscriber
    └── group-2/
        └── example-2.pdf      # Beispiel für Contributor
```

## Systemvoraussetzungen

### Server-Anforderungen
- Apache 2.4 oder höher
- mod_rewrite aktiviert
- PHP 7.4 oder höher
- Ausreichend Speicherplatz für geschützte Dateien

### PHP-Extensionen
- fileinfo (für MIME-Type-Erkennung)
- mbstring (für String-Operationen)
- openssl (für sichere Verbindungen)

### WordPress
- Version 5.0 oder höher
- Aktivierte Benutzerrollen
- Schreibrechte im WordPress-Verzeichnis

## Installation

## 1. Verzeichnisstruktur

Erstellen Sie die folgende Verzeichnisstruktur:

```
secure-files/
├── config/
│   └── secure-config.php
├── group-1/
│   ├── example-1.pdf    # Beispiel für Subscriber
│   └── [weitere Dateien für Subscriber]
└── group-2/
    ├── example-2.pdf    # Beispiel für Contributor
    └── [weitere Dateien für Contributor]
```

## 2. Konfiguration

### 2.1 Rollenzuordnung
```php
$role_mappings = [
    'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
    'contributor' => 'group-2'    // Zugriff auf example-2.pdf
];
```

### 2.2 Berechtigungen

Setzen Sie die korrekten Berechtigungen:

```bash
chmod 755 secure-files
chmod 755 secure-files/config
chmod 755 secure-files/group-1
chmod 755 secure-files/group-2
chmod 644 secure-files/group-1/example-1.pdf
chmod 644 secure-files/group-2/example-2.pdf
```

### 2. Schutz-Ordner hochladen

1. Entpacke das ZIP-Archiv, das du erhalten hast.
2. Lade den gesamten Ordner **`protected`** in dein `public_html`-Verzeichnis hoch.

Danach sollte die Struktur so aussehen:

```
public_html/
├── wordpress/          # WordPress-Installation
└── protected/          # Schutz-Ordner
    ├── .htaccess      # URL-Weiterleitung
    └── check-access.php # Zugriffskontrolle

/secure-files/          # Geschützte Dateien (außerhalb von public_html)
├── config/
│   └── secure-config.php
├── group-1/           # Beispiel für Subscriber
└── group-2/           # Beispiel für Contributor
```

### 3. WordPress-Konfiguration

1. Kopiere die `secure-config.php` in den `config`-Ordner deines geschützten Verzeichnisses.
2. Passe die Konfiguration in der `secure-config.php` an:
   ```php
   // WordPress-Pfad
   define('WP_CORE_PATH', dirname(__DIR__) . '/wordpress/wp-load.php');

   // Rollen und ihre zugehörigen Ordner
   $role_folders = [
       'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
       'contributor' => 'group-2'    // Zugriff auf example-2.pdf
   ];

   // Download-Einstellungen
   define('MAX_DIRECT_DOWNLOAD_SIZE', 524288);  // 512 KB
   define('CHUNK_SIZE', 1048576);              // 1 MB
   ```

**Wichtig:**  
Die Konfigurationsdatei muss vor dem Laden von WordPress eingebunden werden.

**Hinweis:**  
Liegt WordPress direkt in `public_html`, reicht  
```php
define( 'SECURE_FILE_PATH', dirname( ABSPATH ) . '/secure-files' );
```

### 4. Erster Praxistest

1. Melde dich von WordPress ab.
2. Rufe eine der Beispieldateien im Browser auf:
   ```
   https://deine-domain.tld/protected/group-1/example-1.pdf  # für Subscriber
   ```
   oder
   ```
   https://deine-domain.tld/protected/group-2/example-2.pdf  # für Contributor
   ```
3. Du solltest das Login-Formular sehen. Nach dem Einloggen erscheint die geschützte Datei.

**Glückwunsch, dein Tresor funktioniert!**

## Deinstallation

### 1. Dateien entfernen
1. `protected` Ordner aus `public_html` löschen
2. `secure-files` Ordner sichern (falls benötigt)
3. `secure-files` Ordner löschen

### 2. WordPress-Konfiguration
1. `wp-config.php` öffnen
2. `SECURE_FILE_PATH` Definition entfernen

### 3. Aufräumen
1. Temporäre Dateien löschen
2. Cache leeren
3. Logs sichern

## Fehlerhilfe

- **404-Fehler:** Pfad in der URL stimmt nicht mit den Ordnern in `secure-files` überein.
- **Endlose Weiterleitung:** Prüfe Cookies (`www` ↔ ohne `www`) oder den Pfad zu `wp-load.php`.
- **Abgebrochene Downloads:** Erhöhe im Skript die Zeile `fread($fp, 1048576)` z. B. auf `4194304`. 