Mit diesem kleinen „Dateitresor“ kannst du beliebige HTML-, PDF-, Bilder oder andere Downloads sicher auf deinem Server lagern, ohne sie in WordPress selbst hochzuladen.  

Alle Anfragen an diese Dateien laufen zuerst durch eine unsichtbare „Türklingel“ (.htaccess) und anschliessend durch einen „Wachhund“ (check-access.php).  
Der Wachhund prüft: Ist der Besucher bei WordPress eingeloggt und besitzt er die passende Rolle (z. B. _seminar-website-basis_ oder _cv-interessent_)?  
Nur wenn das stimmt, wird die gewünschte Datei aus dem nicht-öffentlichen Ordner **secure-files** gestreamt; ansonsten landet der Besucher auf der Anmeldeseite oder erhält eine Fehlermeldung.  
So bleibt deine Daten selbst dann geschützt, wenn jemand den direkten Link kennt.

## Struktur

```
/home/<account>/public_html/
├── main/ ….              (WordPress‑Installation)
├── protected/ ….         (Wachhund + Klingel)
└── secure-files/         (Geheimer Tresor ausserhalb des WebRoots für die geschützten Dateien)
    ├── s-wsb/ …
    └── secure-docs/ …
```

## Ordner anlegen

1. Melde dich per FTP oder Datei-Manager auf deinem Webspace an.  
2. Wechsle **eine Ebene höher** als `public_html`.  
3. Lege dort den Ordner **`secure-files`** an.  
4. Kopiere alle geheimen Dateien hinein, z. B.:

```
secure-files/s-wsb/de/docs/index.html
secure-files/s-wsb/de/docs/praesentation.pdf
```

### Rollen-Konfiguration
1. Tresor-Ordner secure-files (mit s-wsb / secure-docs) anlegen
2. Ordner protected ins public_html kopieren
3. secure-config.php nach /secure-files/config/ legen
4. Zeile  require ... secure-config.php  ist bereits im Skript eingebaut
5. Zeile in wp-config.php:
   define( 'SECURE_FILE_PATH', dirname( dirname( ABSPATH ) ) . '/secure-files' );

## Schutz-Ordner hochladen

1. Entpacke das ZIP, das du bekommen hast.  
2. Lade den ganzen Ordner **`protected`** in dein `public_html`.  

Jetzt liegt dort:

```
public_html/protected/.htaccess
public_html/protected/check-access.php
```

`.htaccess` ist die Türklingel, `check-access.php` der Wachhund.



## WordPress den Schatz zeigen

Öffne deine **`wp-config.php`** (liegt in `public_html` oder `public_html/main/`).  
Füge oberhalb von

```php
/* That's all, stop editing! Happy publishing. */
```

diese Zeile ein:

```php
define( 'SECURE_FILE_PATH', dirname( dirname( ABSPATH ) ) . '/secure-files' );
```



### Exkurs: Was macht dieser Befehl?

1. **`ABSPATH`** – zeigt auf deinen WordPress-Ordner, z. B.  
   `/home/user/public_html/main/`  
2. **Einmal `dirname()`** – klettert nach oben:  
   `/home/user/public_html/`  
3. **Noch einmal `dirname()`** – klettert nochmals:  
   `/home/user/`  
4. **`.'/secure-files'`** – hängt den Ordnernamen an:  
   `/home/user/secure-files`  
5. **`define()`** – merkt sich diesen Pfad in ganz WordPress, damit der Wachhund weiss, wo der Schatz liegt.

> Liegt WordPress direkt in `public_html`, reicht **ein** `dirname( ABSPATH )`.



## Wie funktionieren die Dateien?

### `.htaccess` — die Türklingel

#### Schritt-für-Schritt-Erklärung

1. **Umschreiben einschalten**
   ```apache
   RewriteEngine On
   RewriteBase /protected/
   ```
   – Schaltet die Umschreib-Funktion an und sagt: „Alle Regeln gelten für `/protected/`“.

2. **Wachhund vor Direktaufrufen schützen**
   ```apache
   RewriteRule ^check-access\.php$ - [F,L]
   ```
   – Wer `…/check-access.php` direkt eingibt, bekommt sofort **403 Verboten**.

3. **Verzeichnis-Aufruf auf `index.html` umbiegen**
   ```apache
   RewriteRule ^(.+/)$ /protected/check-access.php?file=$1index.html [QSA,L]
   ```
   – Tippt jemand `/protected/s-wsb/`, hängt die Regel `index.html` an und ruft den Wachhund.

4. **Alle anderen Dateien an den Wachhund schicken**
   ```apache
   RewriteRule ^(.+)$ /protected/check-access.php?file=$1 [QSA,L]
   ```
   – Egal ob HTML, PDF oder Bild – erst prüft der Wachhund.

5. **Caching verbieten**
   ```apache
   Header set Cache-Control "private, no-cache, no-store, must-revalidate"
   ```
   – Browser sollen die Geheimnisse nicht aus Versehen speichern.



### `check-access.php` — der Wachhund (aktuelle Rollen‑Logik)

*(Alle relevanten Befehle der Datei, jeder mit einer Kurzerklärung.)*

0. **Themen-Engine ausschalten**
   ```php
   define('WP_USE_THEMES', false);
   ```
   – verhindert, dass WordPress Theme‑Code lädt (spart Zeit, wir brauchen hier nur Benutzer‑Funktionen).

1. **WordPress laden**
   ```php
   require_once dirname(__DIR__) . '/main/wp-load.php';
   ```
   – bringt alle WP‑Funktionen ins Spiel.

2. **Tresor‑Root setzen** (Fallback, falls `wp-config.php` vergessen wurde)
   ```php
   if (!defined('SECURE_FILE_PATH')) {
       define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   }
   ```

3. **Rollen → Unterordner**
   ```php
   $role_folders = [
       'seminar-website-basis' => 's-wsb',
       'cv-interessent'        => 'secure-docs'
   ];
   ```

4. **Nur eingeloggte Besucher**
   ```php
   if (!is_user_logged_in()) { auth_redirect(); exit; }
   ```

5. **Dateipfad aus URL holen & säubern**
   ```php
   $rel = $_GET['file'] ?? '';
   $rel = ltrim(str_replace(['..','./','\'], '', $rel), '/');
   if ($rel === '' || substr($rel, -1) === '/') { $rel .= 'index.html'; }
   ```
   *– Entfernt gefährliche Sequenzen („../“), schneidet führende Slashes ab und hängt `index.html` an, wenn nur ein Ordner aufgerufen wurde.*

6. **Ordner‑ und Rollen‑Check**
   ```php
   $current = wp_get_current_user();
   $roles   = $current->roles;
   $allowed = false;

   foreach ($roles as $role) {
       if ($role === 'administrator') { $allowed = true; break; }
       if (isset($role_folders[$role])) {
           $prefix = $role_folders[$role] . '/';
           if (str_starts_with($rel, $prefix)) { $allowed = true; break; }
       }
   }
   if (!$allowed) { status_header(403); exit('Forbidden'); }
   ```

7. **Pfad‑ und Existenz‑Prüfung**
   ```php
   $abs = realpath( SECURE_FILE_PATH . '/' . $rel );
   if ($abs === false || !is_file($abs) || strncmp($abs, SECURE_FILE_PATH, strlen(SECURE_FILE_PATH)) !== 0) {
       status_header(404); exit('not found');
   }
   ```
   *– `realpath()` löst Symlinks; `strncmp()` stellt sicher, dass der Pfad wirklich innerhalb des Tresors bleibt.*

8. **MIME‑Type & sichere Header**
   ```php
   $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
   $mime = [
       'html'=>'text/html','pdf'=>'application/pdf','css'=>'text/css','js'=>'application/javascript',
       'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','svg'=>'image/svg+xml','webp'=>'image/webp'
   ][$ext] ?? 'application/octet-stream';

   header("Content-Type: $mime");
   header('X-Content-Type-Options: nosniff');
   $size = filesize($abs);
   header("Content-Length: $size");
   ```
   *– Richtiger MIME‑Type verhindert Download‑Fehler; `nosniff` stoppt Content‑Type‑Raterei im Browser.*

9. **Datei ausliefern (Chunk‑Streaming für grosse Dateien)**
   ```php
   if ($size > 524288) {          // > 512 kB
       @set_time_limit(0);        // Download darf dauern
       while (ob_get_level()) ob_end_flush();
       $fp = fopen($abs, 'rb');
       if ($fp) {
           while (!feof($fp)) { echo fread($fp, 1048576); flush(); }
           fclose($fp);
       }
       exit;
   }
   readfile($abs);
   exit;
   ```
   *– Grosse Dateien werden in 1‑MiB‑Blöcken gesendet (stabil bei LiteSpeed); kleine via `readfile()`.*



## Erster Praxistest

1. **Abmelden** von WordPress.  
2. Aufrufen:

```
https://deine-domain.tld/protected/s-wsb/de/docs/index.html
```

Du siehst das Login-Formular.  
3. **Einloggen** → Die geheime Seite erscheint.  
Glückwunsch, dein Tresor funktioniert!



## Fehlerhilfe

* **404** – Pfad in der URL stimmt nicht mit Ordnern in `secure-files` überein.  
* **Endlose Weiterleitung** – Cookie-Problem (`www` ↔ ohne `www`) oder falscher Pfad zu `wp-load.php`.  
* **Grosse Downloads brechen ab** – Vergrössere im Skript die Zeile `fread($fp, 1048576)` z. B. auf `4194304`.



## Fertig!

Jetzt hast du eine sichere Schatzkiste für deine Präsentationen, PDFs und alle anderen Dateien – öffnen kann sie nur, wer bei WordPress eingeloggt ist.

Viel Spass beim Ausprobieren!
