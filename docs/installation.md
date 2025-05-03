# Installation

## Schnellstart Installation

1. Release-Dateien herunterladen:
   - [protected.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/secure-files.zip)

2. Dateien entpacken und kopieren:
   - `protected.zip` → Inhalt in das WebRoot-Verzeichnis kopieren (z.B. `public_html/`, `htdocs/` oder `www/`)
   - `secure-files.zip` → Inhalt außerhalb des WebRoots kopieren

3. WordPress konfigurieren:
   ```php
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
   # WebRoot-Verzeichnis (z.B. public_html, htdocs, www)
   /path/to/webroot/
   └── protected/              # Aus protected.zip
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

4. **WordPress-Pfad konfigurieren**
   ```php
   // In secure-files/config/secure-config.php
   
   // Wenn WordPress direkt im WebRoot liegt
   define('WP_CORE_PATH', dirname(__DIR__, 2) . '/wp-load.php');
   
   // Wenn WordPress in einem Unterverzeichnis liegt
   define('WP_CORE_PATH', dirname(__DIR__, 2) . '/main/wp-load.php');
   ```

5. **Debug-Modus (optional)**
   ```php
   // In secure-files/config/secure-config.php
   define('DEBUG_MODE', true);  // Nur für Entwicklung!
   ```

6. **Testen**
   - WordPress-Benutzer anmelden
   - Datei über `/protected/group-1/example-1.pdf` aufrufen
   - Log-Datei auf Fehler prüfen

### Fehlerbehebung

1. **403 Forbidden**
   - WordPress-Benutzer eingeloggt?
   - Richtige Benutzerrolle?
   - Korrekte Verzeichnisstruktur?

2. **404 Not Found**
   - Datei existiert?
   - Pfad korrekt?
   - Berechtigungen richtig?

3. **500 Internal Server Error**
   - PHP-Version ≥ 7.4?
   - Memory-Limit ≥ 128M?
   - WordPress-Pfad korrekt?

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
Nach der Installation könnte Ihre Verzeichnisstruktur so aussehen (Beispiel mit `public_html` als WebRoot):

```
/var/www/                      # Server-Root
├── public_html/               # WebRoot (von außen erreichbar)
│   ├── main/                  # WordPress-Installation
│   └── protected/             # Kopiert aus protected.zip
│       ├── .htaccess          # URL-Weiterleitung
│       └── check-access.php   # Zugriffskontrolle
└── secure-files/              # Außerhalb des WebRoots (nicht erreichbar)
    ├── config/
    │   └── secure-config.php  # Konfigurationsdatei
    ├── logs/
    │   └── access.log        # Log-Datei
    ├── group-1/
    │   └── example-1.pdf      # Beispiel für Subscriber
    └── group-2/
        └── example-2.pdf      # Beispiel für Contributor
```

**Hinweis:**  
- Der Name des WebRoot-Verzeichnisses kann je nach Hosting-Provider variieren (`public_html`, `htdocs`, `www`, etc.)
- Wichtig ist nur, dass `secure-files` **außerhalb** des WebRoots liegt
- Die WordPress-Installation kann direkt im WebRoot oder in einem Unterverzeichnis liegen

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
   - `protected.zip` → Inhalt in das WebRoot-Verzeichnis kopieren (z.B. `public_html/`, `htdocs/` oder `www/`)
   - `secure-files.zip` → Inhalt außerhalb des WebRoots kopieren

2. Überprüfe die Verzeichnisstruktur:
   - In `public_html/` sollte ein `protected/` Ordner sein
   - Außerhalb des WebRoots sollte ein `secure-files/` Ordner sein
   - Erstelle das `logs` Verzeichnis in `secure-files/` falls nicht vorhanden

3. Setze die Berechtigungen:
   ```bash
   chmod 755 secure-files/logs
   chmod 644 secure-files/logs/access.log
   ```

### 2. Konfiguration anpassen

1. Öffne die Datei `secure-files/config/secure-config.php`
2. Passe die Konfiguration an:
   ```php
   // WordPress-Pfad (wenn WordPress in /public_html/main/ liegt)
   define('WP_CORE_PATH', dirname(__DIR__, 2) . '/public_html/main/wp-load.php');

   // Logging-Konfiguration
   define('LOG_DIR', dirname(__DIR__) . '/logs');
   define('LOG_FILE', LOG_DIR . '/access.log');

   // Rollen und ihre zugehörigen Ordner
   $role_folders = [
       'subscriber' => 'group-1',    // Zugriff auf example-1.pdf
       'contributor' => 'group-2'    // Zugriff auf example-2.pdf
   ];

   // Download-Einstellungen
   define('MAX_DIRECT_DOWNLOAD_SIZE', 524288);  // 512 KB
   define('CHUNK_SIZE', 1048576);              // 1 MB
   ```

3. Öffne die WordPress-Konfigurationsdatei `wp-config.php` und füge folgende Zeile hinzu:
   ```php
   // Pfad zum geschützten Dateiverzeichnis
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

**Wichtig:**  
- Die Konfigurationsdatei muss vor dem Laden von WordPress eingebunden werden
- Der Pfad zu `wp-load.php` muss absolut korrekt sein
- Bei einem Fehler "Undefined constant WP_CORE_PATH" ist der Pfad in `secure-config.php` falsch
- Der Eintrag in `wp-config.php` ist **zwingend erforderlich**
- Das Log-Verzeichnis muss beschreibbar sein

**Hinweis:**  
- Wenn WordPress in einem Unterverzeichnis liegt (z.B. `/public_html/main/`), verwende:
  ```php
  define('WP_CORE_PATH', dirname(__DIR__, 2) . '/public_html/main/wp-load.php');
  ```
- Wenn WordPress direkt in `public_html` liegt, verwende:
  ```php
  define('WP_CORE_PATH', dirname(__DIR__, 2) . '/public_html/wp-load.php');
  ```

**Detaillierte Konfiguration:**  
Für eine vollständige Übersicht aller Konfigurationsmöglichkeiten siehe [Konfigurationsdokumentation](configuration.md).

### 3. Erster Praxistest

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

**Fehlerbehebung:**
- Bei "Undefined constant WP_CORE_PATH": Überprüfe den Pfad in `secure-config.php`
- Bei 404-Fehlern: Überprüfe die Verzeichnisstruktur und Berechtigungen
- Bei 403-Fehlern: Überprüfe die WordPress-Rollen und -Berechtigungen
- Bei Logging-Fehlern: Überprüfe die Berechtigungen des Log-Verzeichnisses

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
- **Logging-Fehler:** Überprüfe die Berechtigungen des Log-Verzeichnisses und der Log-Datei. 