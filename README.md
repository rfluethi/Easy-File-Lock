# Website Access Control

Ein WordPress-Plugin zur sicheren Zugriffskontrolle von Dateien ausserhalb des Web-Roots.

## Schnellstart

1. **Plugin installieren**
   ```bash
   # Im WordPress Plugin-Verzeichnis
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/rfluethi/website-access-control.git
   ```

2. **Geschützte Dateien einrichten**
   ```bash
   # Ausserhalb des Web-Roots
   mkdir -p /var/www/secure-files/{admin,editor,author,public}
   chmod 755 /var/www/secure-files
   chown www-data:www-data /var/www/secure-files
   ```

3. **Konfiguration anpassen**
   - Kopieren Sie `config.example.php` nach `config.php`
   - Passen Sie die Pfade und Berechtigungen an

## Was dieses Plugin kann

- **Dateizugriff schützen**: Dateien werden ausserhalb des Web-Roots gespeichert
- **Rollenbasierter Zugriff**: WordPress-Benutzerrollen steuern den Zugriff
- **Sicheres Logging**: Alle Zugriffe werden protokolliert
- **Rate-Limiting**: Verhindert Überlastung durch zu viele Anfragen

## Was dieses Plugin NICHT kann

- Dateien hochladen (muss manuell oder über ein separates System erfolgen)
- Dateien bearbeiten oder löschen
- Automatische Backups erstellen
- Dateien komprimieren oder konvertieren

## Systemanforderungen

- PHP 7.4 oder höher
- WordPress 5.0 oder höher
- Apache mit mod_rewrite
- Schreibrechte für das Logs-Verzeichnis

## Dokumentation

- [Installation](docs/installation.md): Detaillierte Installationsanleitung
- [Konfiguration](docs/configuration.md): Alle Konfigurationsoptionen
- [Technische Details](docs/technical.md): Architektur und Sicherheit
- [Fehlerbehebung](docs/troubleshooting.md): Häufige Probleme und Lösungen

## Support

- GitHub Issues für Fehlermeldungen
- Pull Requests für Verbesserungen
- Kein kommerzieller Support verfügbar

## Lizenz

MIT License - Siehe [LICENSE](LICENSE) für Details
