# Website Access Control Basic

Ein sicheres System zum Verwalten und Ausliefern geschützter Dateien in WordPress.

## Hintergrund

Viele WordPress-Websites benötigen eine Möglichkeit, sensible Dateien (z. B. Kursunterlagen, interne Dokumente) sicher zu speichern und nur bestimmten Benutzergruppen zugänglich zu machen. Standard-WordPress-Mechanismen bieten keinen Schutz für Dateien außerhalb der Mediathek. Dieses Projekt entstand, um eine einfache, sichere und rollenbasierte Lösung für den Dateizugriff zu schaffen.

## Schnellstart

1. [Installation durchführen](docs/installation.md)
2. [Konfiguration anpassen](docs/configuration.md)
3. Geschützte Dateien in den `secure-files` Ordner kopieren
4. Zugriff über `/protected/` testen

## Dokumentation

Die vollständige Dokumentation findest du im `docs/` Verzeichnis:

- [Übersicht](docs/README.md) - Allgemeine Informationen
- [Installation](docs/installation.md) - Schritt-für-Schritt-Anleitung
- [Konfiguration](docs/configuration.md) - Einstellungsmöglichkeiten
- [Technische Details](docs/technical.md) - Funktionsweise
- [Fehlerbehebung](docs/troubleshooting.md) - Lösungen für Probleme
- [Sicherheit](docs/security.md) - Sicherheitshinweise
- [Ablaufdiagramm](docs/flow.md) - Prozessablauf
- [Changelog](docs/changelog.md) - Versionshistorie

## Systemanforderungen

- WordPress 5.0 oder höher
- PHP 7.4 oder höher
- Apache 2.4 oder höher
- mod_rewrite aktiviert
- PHP-Extensionen:
  - fileinfo
  - mbstring
  - openssl

## Sicherheit

Das System bietet mehrschichtige Sicherheit:

1. **Dateisystem-Ebene**
   - Dateien außerhalb des Web-Roots
   - Strikte Berechtigungen
   - Keine direkte URL-Zugänglichkeit

2. **Webserver-Ebene**
   - `.htaccess`-Schutz
   - PHP-Ausführung verhindert
   - Weiterleitung an `check-access.php`

3. **Anwendungs-Ebene**
   - WordPress-Authentifizierung
   - Rollenbasierte Zugriffskontrolle
   - Session-Management

## Features

- **Sicherer Speicherort**: Dateien außerhalb des WebRoots
- **Rollenbasierter Zugriff**: Nur berechtigte Benutzer
- **Flexible Konfiguration**: Einfache Anpassung
- **Effizientes Streaming**: Optimierte Übertragung
- **Performance-Tuning**: Anpassbare Chunk-Größen
- **Debugging-Tools**: Umfangreiche Logging-Optionen

## Installation

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

## Konfiguration

Die Konfiguration erfolgt in `secure-config.php`:

```php
$role_folders = [
    'seminar-website-basis' => 's-wsb',
    'cv-interessent'        => 'secure-docs'
];
```

## Bekannte Probleme

- Große Dateien (>100MB) können bei einigen Hosting-Anbietern zu Timeouts führen
- Einige Hosting-Anbieter erlauben keinen Zugriff außerhalb des WebRoots
- Manche PHP-Konfigurationen limitieren die maximale Dateigröße

## Beitragen

1. Fork erstellen
2. Feature-Branch erstellen (`git checkout -b feature/AmazingFeature`)
3. Änderungen committen (`git commit -m 'Add some AmazingFeature'`)
4. Branch pushen (`git push origin feature/AmazingFeature`)
5. Pull Request erstellen

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE) für Details.

## Support

Bei Fragen oder Problemen:
1. Prüfe die [Fehlerbehebung](docs/troubleshooting.md)
2. Konsultiere die [technische Dokumentation](docs/technical.md)
3. Stelle sicher, dass alle [Sicherheitshinweise](docs/security.md) beachtet wurden
