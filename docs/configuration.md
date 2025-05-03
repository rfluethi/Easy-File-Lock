# Konfigurationsdokumentation

Diese Dokumentation beschreibt die Konfiguration eines Systems zur geschützten Dateiauslieferung auf Basis von WordPress-Rollen. Sie richtet sich an technisch versierte Personen mit Serverzugang und Grundkenntnissen in PHP und WordPress.

## Wichtige Hinweise

### Lade-Reihenfolge

Die Reihenfolge der Einbindungen ist kritisch:

1. `secure-config.php` muss vor allen anderen Komponenten geladen werden.
2. Erst danach dürfen Konstanten wie `MIN_MEMORY_LIMIT` verwendet werden.
3. WordPress wird anschließend über `wp-load.php` geladen.

### Fehlerbehandlung

Wenn `secure-config.php` nicht existiert oder nicht geladen werden kann, muss das Skript mit einer Fehlermeldung beendet werden. Gleiches gilt, wenn der WordPress-Pfad (`WP_CORE_PATH`) nicht korrekt gesetzt oder `wp-load.php` nicht auffindbar ist.

## Einstellungen für Dateiübertragungen

```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
define('MAX_FILE_SIZE', 1024 * 1024 * 1024); // 1 GB
define('MIN_MEMORY_LIMIT', '128M');          // Minimales PHP Memory-Limit
```

**Erläuterung:**

* `MAX_DIRECT_DOWNLOAD_SIZE`: Dateien bis zu dieser Größe werden direkt gesendet. Größere Dateien werden gestreamt.
* `CHUNK_SIZE`: Größe der Datenblöcke beim Streaming. Kleine Werte verlangsamen, große Werte riskieren Speicherprobleme.
* `MAX_FILE_SIZE`: Obergrenze für die zulässige Dateigröße.
* `MIN_MEMORY_LIMIT`: Mindestwert für das PHP-Memory-Limit.

## Logging-Einstellungen

```php
define('LOG_DIR', dirname(__DIR__) . '/logs');
define('LOG_FILE', LOG_DIR . '/access.log');
define('LOG_MAX_SIZE', 5 * 1024 * 1024);  // 5 MB
define('LOG_MAX_FILES', 5);               // Max. 5 Log-Dateien
define('DEBUG_MODE', true);               // Debug-Ausgaben aktivieren
```

**Erläuterung:**

* `LOG_DIR`: Verzeichnis für Logs
* `LOG_FILE`: Pfad zur Log-Datei
* `LOG_MAX_SIZE`: Ab dieser Größe wird rotiert
* `LOG_MAX_FILES`: Maximale Anzahl von Log-Dateien
* `DEBUG_MODE`: Aktiviert zusätzliche Ausgaben ins Log (für Entwicklung empfohlen, in Produktion deaktivieren)

## Sicherheitseinstellungen

```php
define('CACHE_CONTROL', 'private, no-cache, no-store, must-revalidate');

define('SECURITY_HEADERS', [
    'X-Content-Type-Options: nosniff',
    'X-Frame-Options: DENY',
    'X-XSS-Protection: 1; mode=block',
    'Referrer-Policy: strict-origin-when-cross-origin',
    'Content-Security-Policy: default-src \'self\'',
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'
]);
```

**Erläuterung:**

* `CACHE_CONTROL`: Steuert das Caching-Verhalten beim Dateidownload.
* `SECURITY_HEADERS`: Array mit sicherheitsrelevanten HTTP-Headern gegen gängige Angriffsvektoren.

## Rollenbasierter Zugriff auf Verzeichnisse

```php
$role_folders = [
    'subscriber'  => 'group-1',
    'contributor' => 'group-1',
    'author'      => 'group-2'
];
```

* Rollen wie `subscriber` und `contributor` haben Zugriff auf das Verzeichnis `group-1`.
* `author` hat Zugriff auf das Verzeichnis `group-2`.
* Wenn mehrere Rollen Zugriff auf dasselbe Verzeichnis haben sollen, kann der Zielordner mehrfach zugewiesen werden.

## Zugelassene MIME-Typen für Downloads

```php
$allowed_mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
];

$allowed_mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
```

Nur Dateitypen, die in `$allowed_mime_types` definiert sind, dürfen heruntergeladen werden. Zusätzliche Typen können dynamisch ergänzt werden.

**Verhalten bei einem erlaubten MIME-Typ:**

* Die Datei wird – sofern Pfad, Benutzerrolle und andere Bedingungen erfüllt sind – zum Download freigegeben.

**Verhalten bei einem nicht erlaubten MIME-Typ:**

* Der Download wird blockiert.
* Der Zugriff wird abgelehnt, die Anfrage wird protokolliert (sofern Logging aktiv ist).
* Es erfolgt keine Dateiausgabe an den Client.

## Hinweis zur Anpassung

Diese Konfigurationswerte sollten an die jeweilige Serverumgebung angepasst werden. In Produktionsumgebungen sollte `DEBUG_MODE` auf `false` stehen. Sicherheits-Header und Rollen sollten regelmäßig überprüft und bei Bedarf erweitert werden.
