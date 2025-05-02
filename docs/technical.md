# Technische Dokumentation: Dateitresor

## Inhaltsverzeichnis

1. [Überblick](#überblick)
2. [Systemarchitektur](#systemarchitektur)
3. [Dateisystem-Struktur](#dateisystem-struktur)
4. [Technische Komponenten](#technische-komponenten)
5. [Sicherheitsmaßnahmen](#sicherheitsmaßnahmen)
6. [Performance-Optimierung](#performance-optimierung)
7. [Fehlerbehandlung](#fehlerbehandlung)
8. [Best Practices](#best-practices)
9. [Debugging](#debugging)

## Überblick

Der Dateitresor ist ein sicheres System zum Verwalten und Ausliefern geschützter Dateien in WordPress. Er ermöglicht:

- Sichere Speicherung von Dateien außerhalb des WebRoots
- Rollenbasierte Zugriffskontrolle
- Effizientes Streaming großer Dateien
- Schutz vor unbefugtem Zugriff

## Systemarchitektur

### Komponenten

1. **Dateisystem**
   - Geschützter Speicherort außerhalb des WebRoots
   - Rollenbasierte Ordnerstruktur
   - Konfigurationsdateien

2. **Webserver**
   - `.htaccess` für URL-Weiterleitung
   - PHP-Ausführung verhindert
   - Verzeichnisauflistung deaktiviert

3. **WordPress-Integration**
   - Authentifizierung
   - Rollenverwaltung
   - Pfadkonfiguration

## Dateisystem-Struktur

```
/secure-files/          # Hauptordner für geschützte Dateien (außerhalb des WebRoots)
├── config/            # Konfigurationsordner (755)
│   └── secure-config.php  # Konfigurationsdatei (644)
└── [role-folders]/    # Rollenordner (755)
    ├── group-1/       # Verzeichnis für seminar-website-basis (subscriber)
    └── group-2/       # Verzeichnis für cv-interessent (contributor)
```

### Berechtigungen

- Ordner: `755` (drwxr-xr-x)
- Dateien: `644` (-rw-r--r--)
- Konfigurationsdateien: `644` (-rw-r--r--)

## Technische Komponenten

### 1. .htaccess — URL-Weiterleitung

```apache
# PHP-Ausführung verhindern
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Verzeichnisauflistung verhindern
Options -Indexes

# Weiterleitung an check-access.php
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^(.*)$ check-access.php?file=$1 [L,QSA]
```

### 2. check-access.php — Zugriffskontrolle

#### WordPress-Integration
```php
require_once WP_CORE_PATH;    // load WordPress core
```
- Lädt WordPress-Umgebung
- Stellt Authentifizierung bereit
- Macht WordPress-Funktionen verfügbar

#### Pfad-Konstruktion
```php
$requested_file = $_GET['file'];
$secure_path = SECURE_FILE_PATH;
$full_path = $secure_path . '/' . $requested_file;
```
- Liest Dateipfad aus URL
- Verwendet WordPress-Konstante
- Konstruiert vollständigen Pfad

#### Zugriffskontrolle
```php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
    exit;
}
```
- Prüft Login-Status
- Leitet zum Login weiter
- Speichert Rückkehrpfad

#### Rollenprüfung
```php
$user = wp_get_current_user();
$role = $user->roles[0];
$role_folders = [
    'subscriber' => 'group-1',    // seminar-website-basis
    'contributor' => 'group-2'    // cv-interessent
];
$role_folder = $role_folders[$role];
```
- Holt Benutzerinformationen
- Liest primäre Rolle
- Mappt auf Ordnerstruktur

#### Pfadvalidierung
```php
if (strpos($requested_file, $role_folder) !== 0) {
    wp_die('Zugriff verweigert');
}
```
- Prüft Rollenzugehörigkeit
- Verhindert Ordnerüberschreitung
- Gibt Fehlermeldung aus

#### MIME-Type-Erkennung
```php
$mime_type = mime_content_type($full_path);
if (!isset($allowed_mime_types[$mime_type])) {
    wp_die('Dateityp nicht erlaubt');
}
```
- Erkennt Dateityp
- Prüft Erlaubnis
- Verhindert Ausführung

#### Datei-Streaming
```php
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($full_path));
readfile($full_path);
```
- Setzt Header
- Streamt Datei
- Optimiert Übertragung

#### Chunked Download
```php
$fp = fopen($full_path, 'rb');
while (!feof($fp)) {
    echo fread($fp, CHUNK_SIZE);
    flush();
}
fclose($fp);
```
- Öffnet Datei
- Liest in Chunks
- Verhindert Überlauf

### 2. Konfigurationsdatei (secure-config.php)

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

// Erlaubte Dateiendungen und ihre MIME-Types
$allowed_mime_types = [
    'html' => 'text/html',
    'pdf'  => 'application/pdf',
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'webp' => 'image/webp'
];

// Cache-Einstellungen
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

// Debug-Modus (nur für Entwicklung!)
define('DEBUG_MODE', false);
```

Die Konfigurationsdatei enthält alle wichtigen Einstellungen für den Dateitresor:
- WordPress-Integration
- Rollenbasierte Zugriffskontrolle
- Download-Optimierungen
- MIME-Type-Definitionen
- Cache-Verhalten
- Debugging-Optionen

## Sicherheitsmaßnahmen

### 1. Dateisystem
- Außerhalb des WebRoots
- Strikte Berechtigungen
- Keine PHP-Ausführung

### 2. Zugriffskontrolle
- WordPress-Login erforderlich
- Rollenbasierte Einschränkung
- Pfadvalidierung

### 3. Datei-Handling
- MIME-Type-Validierung
- Verzeichnistraversal-Schutz
- Größenbeschränkungen

## Performance-Optimierung

### 1. Chunked Downloads
- Optimale Chunk-Größe: 1 MB (1048576 Bytes)
- Direkte Downloads bis 512 KB (524288 Bytes)
- Sofortiges Flushing für große Dateien

### 2. Caching
- Browser-Cache deaktiviert
- Server-Cache optimiert
- Session-Cache genutzt

### 3. Ressourcen
- Minimale PHP-Auslastung
- Effiziente Dateioperationen
- Optimierte Header

## Fehlerbehandlung

### 1. Zugriffsfehler
- 404: Datei nicht gefunden
- 403: Zugriff verweigert
- 401: Nicht eingeloggt

### 2. Systemfehler
- PHP-Fehler
- Dateisystem-Fehler
- Konfigurationsfehler

### 3. Benutzerfehler
- Falsche URLs
- Ungültige Rollen
- Fehlende Berechtigungen

## Best Practices

### 1. Installation
- Korrekte Ordnerstruktur
- Richtige Berechtigungen
- Sichere Konfiguration

### 2. Wartung
- Regelmäßige Updates
- Log-Analyse
- Backup-Strategie

### 3. Monitoring
- Zugriffsprotokolle
- Fehlerprotokolle
- Performance-Metriken

## Debugging

### Debug-Modus

Der Debug-Modus kann in der `secure-config.php` aktiviert werden:
```php
define('DEBUG_MODE', true);
```

Im Debug-Modus werden folgende Informationen protokolliert:
- Authentifizierungsstatus
- Benutzerrollen und Zugriffsprüfungen
- Dateipfade und Zugriffsversuche
- PHP-Fehler und Warnungen

**Wichtig:**  
Der Debug-Modus sollte nur in Entwicklungsumgebungen aktiviert werden!

### Logging

Debug-Informationen werden in die PHP-Fehlerprotokolle geschrieben:
- Apache: `/var/log/apache2/error.log`
- PHP: `/var/log/php/error.log`
- WordPress: `wp-content/debug.log`

### Fehlerbehandlung

Im Debug-Modus werden detaillierte Fehlermeldungen angezeigt:
- Authentifizierungsfehler
- Zugriffsverweigerungen
- Dateisystem-Fehler
- PHP-Fehler

---

## Beispiel-Ablauf

### Sequenzdiagramm

```mermaid
%%{init: {'theme': 'dark', 'themeVariables': { 'primaryColor': '#4a9eff', 'primaryTextColor': '#ffffff', 'primaryBorderColor': '#4a9eff', 'lineColor': '#4a9eff', 'secondaryColor': '#2d2d2d', 'tertiaryColor': '#2d2d2d'}}}%%
sequenceDiagram
    participant B as Browser
    participant A as Apache
    participant C as check-access.php
    participant W as WordPress
    participant F as Dateisystem

    B->>A: Anfrage geschützte Datei
    Note over B: z.B. /protected/group-1/index.html
    A->>C: Weiterleitung via .htaccess
    
    C->>W: WordPress laden
    W-->>C: WordPress-Umgebung
    
    C->>C: Benutzer eingeloggt?
    alt Nicht eingeloggt
        C->>B: Weiterleitung Login
        B->>W: Login durchführen
        W-->>B: Login erfolgreich
        B->>A: Erneute Anfrage
    end
    
    C->>C: Benutzerrolle prüfen
    C->>C: Pfad validieren
    
    alt Ungültige Rolle
        C->>B: 403 Forbidden
    else Ungültiger Pfad
        C->>B: 404 Not Found
    else Datei nicht gefunden
        C->>B: 404 Not Found
    else Erfolg
        C->>F: Datei öffnen
        Note over F: /secure-files/group-1/index.html
        F-->>C: Datei-Handle
        
        loop Chunk-Streaming
            C->>F: Chunk lesen
            F-->>C: Daten
            C->>B: Chunk senden
        end
        
        C->>F: Datei schließen
        C->>B: Download abgeschlossen
    end
```

**Erklärung des Sequenzdiagramms:**
Dieses Diagramm zeigt die zeitliche Abfolge der Interaktionen zwischen den verschiedenen Systemkomponenten:

1. **Browser → Apache → check-access.php**
   - Browser sendet Anfrage für geschützte Datei
   - Apache leitet Anfrage an check-access.php weiter
   - Weiterleitung erfolgt über .htaccess-Regeln

2. **WordPress-Integration**
   - check-access.php lädt WordPress-Umgebung
   - Ermöglicht Zugriff auf WordPress-Funktionen
   - Stellt Authentifizierung bereit

3. **Authentifizierungsprozess**
   - Prüfung des Login-Status
   - Bei nicht eingeloggten Benutzern: Weiterleitung zum Login
   - Nach erfolgreichem Login: Erneute Anfrage

4. **Zugriffskontrolle**
   - Prüfung der Benutzerrolle
   - Validierung des Dateipfads
   - Mögliche Fehlermeldungen (403/404)

5. **Datei-Streaming**
   - Öffnen der Datei im Dateisystem
   - Chunk-weises Lesen und Senden
   - Optimierte Übertragung großer Dateien

### Flussdiagramm

```mermaid
graph TD
    A[Benutzer] -->|Anfrage| B{Authentifizierung}
    B -->|Nicht eingeloggt| C[Login-Seite]
    C -->|Login| B
    B -->|Eingeloggt| D{Berechtigung prüfen}
    D -->|Keine Berechtigung| E[403 Fehler]
    D -->|Datei nicht gefunden| F[404 Fehler]
    D -->|Berechtigt| G[Datei wird gestreamt]
    G --> H[Download abgeschlossen]

    style A fill:#f9f,stroke:#333,stroke-width:2px
    style B fill:#bbf,stroke:#333,stroke-width:2px
    style D fill:#bbf,stroke:#333,stroke-width:2px
    style G fill:#bfb,stroke:#333,stroke-width:2px
    style E fill:#fbb,stroke:#333,stroke-width:2px
    style F fill:#fbb,stroke:#333,stroke-width:2px
```

**Erklärung des Flussdiagramms:**
Dieses Diagramm zeigt die logische Abfolge der Entscheidungen und Prozesse:

1. **Anfrage**
   - Benutzer fordert geschützte Datei an

2. **Authentifizierung**
   - Prüfung ob Benutzer eingeloggt ist
   - Bei nicht eingeloggten Benutzern: Weiterleitung zum Login

3. **Berechtigungsprüfung**
   - Prüfung der Benutzerrolle
   - Validierung des Dateipfads
   - Sicherheitsüberprüfungen

4. **Ergebnis**
   - Erfolg: Datei wird gestreamt
   - Fehler: 403 (keine Berechtigung) oder 404 (Datei nicht gefunden)

### Unterschiede der Diagramme

- **Sequenzdiagramm**: Zeigt die zeitliche Abfolge und Interaktionen zwischen den Systemkomponenten
- **Flussdiagramm**: Zeigt die logische Abfolge der Entscheidungen und Prozesse

Beide Diagramme ergänzen sich und bieten unterschiedliche Perspektiven auf den gleichen Prozess.

### Sicherheitsaspekte

- Keine direkte URL-Zugänglichkeit
- Rollenbasierte Zugriffskontrolle
- Pfadvalidierung gegen Directory Traversal
- Sichere Dateiübertragung
- Keine PHP-Ausführung in geschützten Verzeichnissen
