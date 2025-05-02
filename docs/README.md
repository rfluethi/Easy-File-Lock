# Dateitresor für WordPress

Ein sicheres System zum Verwalten und Ausliefern geschützter Dateien in WordPress.

## Version
Aktuelle Version: 1.0.0

## Systemanforderungen

- WordPress 5.0 oder höher
- PHP 7.4 oder höher
- Apache 2.4 oder höher
- mod_rewrite aktiviert
- PHP-Extensionen:
  - fileinfo
  - mbstring
  - openssl

## Übersicht

Der Dateitresor ermöglicht es, beliebige Dateien (HTML, PDF, Bilder, etc.) sicher auf deinem Server zu lagern und nur berechtigten WordPress-Benutzern zugänglich zu machen.

### Hauptfunktionen

- **Sicherer Speicherort**: Dateien werden außerhalb des WebRoots gespeichert
- **Rollenbasierter Zugriff**: Nur eingeloggte Benutzer mit passender Rolle können auf Dateien zugreifen
- **Flexible Konfiguration**: Einfache Anpassung von Rollen, Dateitypen und Download-Einstellungen
- **Effizientes Streaming**: Optimierte Übertragung auch für große Dateien

## Dokumentation

Die Dokumentation ist in mehrere Teile aufgeteilt:

1. [Installation](installation.md) - Schritt-für-Schritt-Anleitung zur Einrichtung
2. [Konfiguration](configuration.md) - Detaillierte Einstellungsmöglichkeiten
3. [Technische Details](technical.md) - Tiefere Einblicke in die Funktionsweise
4. [Fehlerbehebung](troubleshooting.md) - Lösungen für häufige Probleme
5. [Sicherheit](security.md) - Sicherheitshinweise und Best Practices

## Schnellstart

1. [Installation durchführen](installation.md)
2. [Konfiguration anpassen](configuration.md)
3. Geschützte Dateien in den `secure-files` Ordner kopieren
4. Zugriff über `/protected/` testen

## Bekannte Probleme

- Große Dateien (>100MB) können bei einigen Hosting-Anbietern zu Timeouts führen
- Einige Hosting-Anbieter erlauben keinen Zugriff außerhalb des WebRoots
- Manche PHP-Konfigurationen limitieren die maximale Dateigröße

## Changelog

### Version 1.0.0
- Erste stabile Version
- Grundlegende Funktionalität implementiert
- Dokumentation erstellt

## Support

Bei Fragen oder Problemen:
1. Prüfe die [Fehlerbehebung](troubleshooting.md)
2. Konsultiere die [technische Dokumentation](technical.md)
3. Stelle sicher, dass alle [Sicherheitshinweise](security.md) beachtet wurden

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE) für Details. 