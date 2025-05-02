# Fehlerbehebung

Hier findest du Lösungen für häufige Probleme und Fehlermeldungen.

## Zugriffsprobleme

### 404 - Datei nicht gefunden

**Symptome:**
- Browser zeigt "404 Not Found"
- Datei existiert, ist aber nicht erreichbar

**Mögliche Ursachen:**
1. Falscher Pfad in der URL
2. Datei liegt nicht im richtigen Ordner
3. WordPress-Konstante `SECURE_FILE_PATH` ist falsch gesetzt

**Lösungen:**
1. Prüfe die URL-Struktur:
   ```
   https://deine-domain.tld/protected/group-1/index.html
   ```
2. Stelle sicher, dass die Datei im richtigen Ordner liegt:
   ```
   /secure-files/group-1/index.html
   ```
3. Überprüfe die `wp-config.php`:
   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

### 403 - Zugriff verweigert

**Symptome:**
- Browser zeigt "403 Forbidden"
- Login-Funktioniert, aber Datei ist nicht zugänglich

**Mögliche Ursachen:**
1. Benutzer hat nicht die richtige Rolle
2. Rolle ist nicht in `secure-config.php` konfiguriert
3. Datei liegt im falschen Ordner für die Rolle

**Lösungen:**
1. Prüfe die Benutzerrolle in WordPress
2. Überprüfe die Rollen-Konfiguration:
   ```php
   $role_folders = [
       'subscriber' => 'group-1',    // seminar-website-basis
       'contributor' => 'group-2'    // cv-interessent
   ];
   ```
3. Stelle sicher, dass die Datei im richtigen Ordner liegt:
   ```
   /secure-files/group-1/index.html  # für subscriber
   /secure-files/group-2/document.pdf # für contributor
   ```

### Endlose Weiterleitung

**Symptome:**
- Browser wird ständig zum Login weitergeleitet
- Login funktioniert nicht richtig

**Mögliche Ursachen:**
1. Cookie-Problem (www vs. non-www)
2. Falscher Pfad zu `wp-load.php`
3. WordPress-Session-Problem

**Lösungen:**
1. Prüfe die Domain-Einstellungen in WordPress
2. Überprüfe den Pfad in `check-access.php`:
   ```php
   require_once WP_CORE_PATH;
   ```
3. Lösche Browser-Cookies und Cache

## Download-Probleme

### Abgebrochene Downloads

**Symptome:**
- Große Dateien werden nicht vollständig heruntergeladen
- Download bricht mitten drin ab

**Mögliche Ursachen:**
1. PHP-Zeitlimit zu niedrig
2. Chunk-Größe zu groß/klein
3. Server-Timeout

**Lösungen:**
1. Erhöhe die Chunk-Größe in `secure-config.php`:
   ```php
   define('CHUNK_SIZE', 4194304);              // 4 MB
   define('MAX_DIRECT_DOWNLOAD_SIZE', 524288); // 512 KB
   ```

### Falsche MIME-Types

**Symptome:**
- Browser zeigt Dateien falsch an
- Downloads starten nicht korrekt

**Mögliche Ursachen:**
1. Fehlender MIME-Type in der Konfiguration
2. Falsche Dateiendung

**Lösungen:**
1. Füge den MIME-Type in `secure-config.php` hinzu:
   ```php
   $allowed_mime_types = [
       'html' => 'text/html',
       'pdf'  => 'application/pdf',
       // ... weitere MIME-Types ...
       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
   ];
   ```
2. Prüfe die Dateiendung

## Server-Probleme

### 500 Internal Server Error

**Symptome:**
- Server meldet internen Fehler
- Keine spezifische Fehlermeldung

**Mögliche Ursachen:**
1. PHP-Fehler in der Konfiguration
2. Falsche Dateiberechtigungen
3. Fehlende PHP-Erweiterungen

**Lösungen:**
1. Aktiviere den Debug-Modus:
   ```php
   define('DEBUG_MODE', true);
   ```
2. Prüfe die Dateiberechtigungen:
   ```
   secure-files: 755
   config: 755
   secure-config.php: 644
   ```
3. Stelle sicher, dass alle benötigten PHP-Erweiterungen aktiviert sind

### Performance-Probleme

**Symptome:**
- Langsame Downloads
- Hohe Serverlast

**Mögliche Ursachen:**
1. Zu kleine Chunk-Größe
2. Zu viele gleichzeitige Downloads
3. Server-Ressourcen erschöpft

**Lösungen:**
1. Optimiere die Chunk-Größe
2. Implementiere Download-Limits
3. Prüfe Server-Ressourcen

### Cache-Probleme

**Symptome:**
- Dateien werden im Browser gecacht
- Änderungen sind nicht sofort sichtbar
- Alte Versionen werden angezeigt

**Mögliche Ursachen:**
1. Browser-Cache nicht deaktiviert
2. Cache-Header nicht korrekt gesetzt
3. Proxy-Cache aktiv

**Lösungen:**
1. Überprüfe die Cache-Einstellungen in `secure-config.php`:
   ```php
   define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');
   ```