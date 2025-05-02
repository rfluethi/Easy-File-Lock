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

- [Installation](docs/installation.md) - Schritt-für-Schritt-Anleitung
- [Konfiguration](docs/configuration.md) - Einstellungsmöglichkeiten
- [Technische Details](docs/technical.md) - Funktionsweise
- [Fehlerbehebung](docs/troubleshooting.md) - Lösungen für Probleme
- [Sicherheit](docs/security.md) - Sicherheitshinweise
- [Changelog](docs/changelog.md) - Versionshistorie

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
