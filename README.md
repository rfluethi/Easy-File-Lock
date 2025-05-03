# Website Access Control Basic

Ein sicheres System zum Verwalten und Ausliefern geschützter Dateien in WordPress.

## Features

- Sichere Speicherung von Dateien außerhalb des WebRoots
- Rollenbasierte Zugriffskontrolle
- Effizientes Streaming großer Dateien
- Schutz vor unbefugtem Zugriff

## Installation

### Schnellstart

1. Laden Sie die aktuellen Release-Dateien herunter:
   - [protected.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/protected.zip)
   - [secure-files.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/secure-files.zip)

2. Entpacken Sie die Dateien:
   - `protected/` in `public_html/` kopieren
   - `secure-files/` außerhalb des WebRoots anlegen

3. WordPress konfigurieren:
   ```php
   define('SECURE_FILE_PATH', dirname(dirname(ABSPATH)) . '/secure-files');
   ```

### Detaillierte Anleitung

Siehe [Installationsanleitung](docs/installation.md) für eine detaillierte Beschreibung.

## Dokumentation

- [Technische Dokumentation](docs/technical.md)
- [Konfiguration](docs/configuration.md)
- [Fehlerbehebung](docs/troubleshooting.md)

## Releases

### Aktuelle Version
- Version: [v1.0.0](https://github.com/your-username/Website-Access-Control-Basic/releases/latest)
- Downloads:
  - [protected.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/protected.zip)
  - [secure-files.zip](https://github.com/your-username/Website-Access-Control-Basic/releases/latest/download/secure-files.zip)

### Ältere Versionen
Alle Releases finden Sie in der [Release-Übersicht](https://github.com/your-username/Website-Access-Control-Basic/releases).

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
