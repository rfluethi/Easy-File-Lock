# Fehlerbehebung

Diese Anleitung unterstützt Administrator\:innen und Entwickler\:innen beim Diagnostizieren und Beheben typischer Probleme im Zugriffssystem geschützter Dateien.

## Häufige Probleme

### Zugriffsprobleme

#### Problem: Benutzer kann nicht auf Dateien zugreifen

**Schritte zur Behebung:**

1. Öffne `secure-config.php` und überprüfe die Rollenzuordnung:

   ```php
   $role_mappings = [
       'subscriber' => 'group-1',
       'contributor' => 'group-2'
   ];
   ```
2. Vergewissere dich, dass der Benutzer in WordPress die korrekte Rolle besitzt.
3. Prüfe die Verzeichnisberechtigungen (mind. 755 für Ordner, 644 für Dateien).
4. Konsultiere bei Bedarf die Log-Datei unter: `secure-files/logs/access.log`

#### Problem: Datei wird nicht gefunden

**Schritte zur Behebung:**

1. Überprüfe die URL (z. B. `/protected/group-1/example-1.pdf`).
2. Stelle sicher, dass die Datei im passenden Gruppenverzeichnis liegt.
3. Überprüfe, ob die Datei korrekt benannt und lesbar ist (Berechtigung 644).

### Fehler 404 – Datei nicht gefunden

**Mögliche Ursachen:**

* Falsche URL
* Datei liegt im falschen Ordner
* Falsche `SECURE_FILE_PATH`-Konstante

**Lösungen:**

1. Vergleiche die URL mit der Serverstruktur:

   ```
   /secure-files/group-1/example-1.pdf  // für Subscriber
   /secure-files/group-2/example-2.pdf  // für Contributor
   ```
2. Überprüfe in `wp-config.php`:

   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```
3. Prüfe auf Tippfehler im Dateinamen.

### Fehler 403 – Zugriff verweigert

**Mögliche Ursachen:**

* Benutzerrolle fehlt oder ist nicht korrekt zugewiesen
* Rolle nicht im Zugriffsskript registriert
* Datei liegt im falschen Verzeichnis

**Lösungen:**

1. Prüfe die Benutzerrolle in WordPress.
2. Kontrolliere die Rollen-Konfiguration in `secure-config.php`:

   ```php
   $role_folders = [
       'subscriber' => 'group-1',
       'contributor' => 'group-2'
   ];
   ```
3. Stelle sicher, dass Datei und Rolle übereinstimmen.

### Endlose Weiterleitung (Loop)

**Mögliche Ursachen:**

* Cookie-Konflikt zwischen www / non-www
* Falscher Pfad zu `wp-load.php`
* WordPress-Session ungültig

**Lösungen:**

1. Prüfe die Domain-Konfiguration in WordPress.
2. Verifiziere in `check-access.php`:

   ```php
   require_once WP_CORE_PATH;
   ```
3. Leere den Browser-Cache und lösche Cookies.

## Download-Probleme

### Abgebrochene Downloads

**Ursachen:**

* Zeitlimit in PHP überschritten
* Ungünstige Chunk-Größe
* Netzwerkprobleme / Verbindungs-Timeouts

**Lösungen:**

1. Passe `CHUNK_SIZE` und `MAX_DIRECT_DOWNLOAD_SIZE` in `secure-config.php` an:

   ```php
   define('CHUNK_SIZE', 4194304);              // 4 MB
   define('MAX_DIRECT_DOWNLOAD_SIZE', 524288); // 512 KB
   ```
2. Server-Konfiguration auf Timeout prüfen (`max_execution_time`).

### Falsche MIME-Types

**Symptome:**

* Datei wird im Browser angezeigt statt heruntergeladen
* Fehlermeldung „nicht unterstützt“

**Lösungen:**

1. Trage fehlende MIME-Types in `secure-config.php` ein:

   ```php
   $allowed_mime_types = [
       'pdf' => 'application/pdf',
       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
       'html' => 'text/html'
   ];
   ```
2. Prüfe Dateiendungen und Dateityp-Konsistenz.

## Server-Probleme

### Fehler 500 – Interner Serverfehler

**Mögliche Ursachen:**

* Fehlerhafte PHP-Konfiguration
* Falsche Berechtigungen
* Fehlende Extensions

**Lösungen:**

1. Aktiviere den Debug-Modus:

   ```php
   define('DEBUG_MODE', true);
   ```
2. Prüfe Berechtigungen:

   ```
   secure-files: 755
   config: 755
   secure-config.php: 644
   ```
3. Stelle sicher, dass Extensions wie `fileinfo`, `mbstring` und `openssl` aktiv sind.

### Langsame Downloads / Performance

**Ursachen:**

* Chunk-Größe zu klein
* Hohe gleichzeitige Last
* Ressourcenbeschränkung auf Serverebene

**Lösungen:**

1. Erhöhe `CHUNK_SIZE` schrittweise.
2. Nutze Server-seitiges Rate-Limiting.
3. Analysiere Serverauslastung (RAM, CPU, Disk I/O).

### Cache-Probleme

**Symptome:**

* Änderungen an Dateien werden nicht angezeigt
* Alte Versionen im Browser

**Lösungen:**

1. Überprüfe die Cache-Control-Einstellungen:

   ```php
   define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');
   ```
2. Lösche Browser-Cache, ggf. Cache-Plugin deaktivieren

## Schnellcheck: Zugriffsfehler

* [ ] Benutzer ist eingeloggt?
* [ ] Rolle korrekt konfiguriert?
* [ ] Datei liegt im richtigen Gruppenordner?
* [ ] MIME-Type erlaubt?
* [ ] Berechtigungen korrekt gesetzt?
* [ ] Zugriff wird in `access.log` dokumentiert?

## Hinweis

Für weiterführende Diagnosen empfiehlt sich die regelmäßige Prüfung der Log-Datei:

```
/secure-files/logs/access.log
```

Diese Datei enthält Hinweise zu Blockierungen, Zugriffen und Fehlermeldungen.
