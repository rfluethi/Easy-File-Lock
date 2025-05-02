# Installation des Dateitresors

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
4. Kopiere alle geheimen Dateien hinein, z. B.:
   - `secure-files/s-wsb/de/docs/index.html`
   - `secure-files/s-wsb/de/docs/praesentation.pdf`

### 2. Schutz-Ordner hochladen

1. Entpacke das ZIP-Archiv, das du erhalten hast.
2. Lade den gesamten Ordner **`protected`** in dein `public_html`-Verzeichnis hoch.

Danach sollte die Struktur so aussehen:

```
public_html/protected/.htaccess
public_html/protected/check-access.php
```

- `.htaccess` ist die "Türklingel" (leitet Anfragen um)
- `check-access.php` ist der "Wachhund" (prüft Zugriffsrechte)

### 3. WordPress-Konfiguration

Diese Datei liegt in deinem WordPress-Hauptverzeichnis (`public_html` oder `public_html/main/`).

```php
// Füge diese Zeile oberhalb von "/* That's all, stop editing! */" ein:
define( 'SECURE_FILE_PATH', dirname( dirname( ABSPATH ) ) . '/secure-files' );
```

**Wichtig:**  
Füge diese Zeile oberhalb von  
```php
/* That's all, stop editing! Happy publishing. */
```
ein.

**Hinweis:**  
Liegt WordPress direkt in `public_html`, reicht  
```php
define( 'SECURE_FILE_PATH', dirname( ABSPATH ) . '/secure-files' );
```

### 4. Erster Praxistest

1. Melde dich von WordPress ab.
2. Rufe eine geschützte Datei im Browser auf, z. B.:
   ```
   https://deine-domain.tld/protected/s-wsb/de/docs/index.html
   ```
3. Du solltest das Login-Formular sehen. Nach dem Einloggen erscheint die geheime Datei.

**Glückwunsch, dein Tresor funktioniert!**

## Upgrade von älteren Versionen

### Vor dem Upgrade
1. Backup erstellen:
   - `secure-files` Ordner sichern
   - `protected` Ordner sichern
   - WordPress-Datenbank sichern

### Upgrade durchführen
1. Neue Version herunterladen
2. `protected` Ordner aktualisieren
3. Konfiguration prüfen
4. Test durchführen

### Nach dem Upgrade
1. Logs prüfen
2. Zugriffe testen
3. Performance überwachen

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