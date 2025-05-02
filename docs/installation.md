# Installation des Dateitresors

## Schnellstart Installation

1. Repository klonen:
   ```bash
   git clone https://github.com/your-username/Website-Access-Control-Basic.git
   ```

2. Dateien hochladen:
   - `protected/` in `public_html/` kopieren
   - `secure-files/` außerhalb des WebRoots anlegen

3. WordPress konfigurieren:
   ```php
   define( 'SECURE_FILE_PATH', dirname( dirname( ABSPATH ) ) . '/secure-files' );
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

### 1. Ordnerstruktur anlegen

1. Melde dich per FTP oder Datei-Manager auf deinem Webspace an.
2. Wechsle **eine Ebene höher** als `public_html`.
3. Lege dort den Ordner **`secure-files`** an.
4. Erstelle die Gruppenordner:
   ```
   /secure-files/
   ├── config/
   │   └── secure-config.php
   ├── group-1/           # seminar-website-basis
   └── group-2/           # cv-interessent
   ```
5. Kopiere alle geschützten Dateien in die entsprechenden Gruppenordner, z. B.:
   - `secure-files/group-1/index.html`
   - `secure-files/group-1/praesentation.pdf`
   - `secure-files/group-2/document.pdf`

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
├── group-1/           # seminar-website-basis
└── group-2/           # cv-interessent
```

### 3. WordPress-Konfiguration

1. Kopiere die `secure-config.php` in den `config`-Ordner deines geschützten Verzeichnisses.
2. Passe die Konfiguration in der `secure-config.php` an:
   ```php
   // WordPress-Pfad
   define('WP_CORE_PATH', dirname(__DIR__) . '/wordpress/wp-load.php');

   // Rollen und ihre zugehörigen Ordner
   $role_folders = [
       'subscriber' => 'group-1',    // seminar-website-basis
       'contributor' => 'group-2'    // cv-interessent
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
2. Rufe eine geschützte Datei im Browser auf, z. B.:
   ```
   https://deine-domain.tld/protected/group-1/index.html
   ```
   oder
   ```
   https://deine-domain.tld/protected/group-2/document.pdf
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