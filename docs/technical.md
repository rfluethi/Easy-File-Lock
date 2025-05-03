# Technische Dokumentation

## 1. Systemarchitektur

### 1.1 Verzeichnisstruktur
```
secure-files/
├── config/
│   └── secure-config.php
├── group-1/
│   ├── example-1.pdf    # Beispiel für Subscriber
│   └── [weitere Dateien für Subscriber]
└── group-2/
    ├── example-2.pdf    # Beispiel für Contributor
    └── [weitere Dateien für Contributor]
```

### 1.2 Dateiübertragung
- Direkte Downloads bis 1 MB
- Chunked Downloads für größere Dateien (4 MB Chunks)
- Optimierte Pufferung und Flush-Mechanismen
- Fortschrittsüberwachung im Debug-Modus

### 1.3 Beispiel-URLs
- Subscriber-Zugriff: `/protected/group-1/example-1.pdf`
- Contributor-Zugriff: `/protected/group-2/example-2.pdf`

### 1.3 Sicherheitsmaßnahmen
- Strikte MIME-Type-Validierung
- Erweiterte Sicherheits-Header:
  - X-Content-Type-Options
  - X-Frame-Options
  - X-XSS-Protection
  - Referrer-Policy
  - Content-Security-Policy
- Rollenbasierte Zugriffskontrolle
- Cache-Kontrolle

## 2. Konfiguration

### 2.1 Download-Einstellungen
```php
define('MAX_DIRECT_DOWNLOAD_SIZE', 1048576);  // 1 MB
define('CHUNK_SIZE', 4194304);               // 4 MB
```

### 2.2 Erlaubte Dateitypen
- Dokumente: PDF, DOC, DOCX
- Tabellen: XLS, XLSX
- Präsentationen: PPT, PPTX
- Bilder: PNG, JPG, GIF, SVG, WEBP
- Web: HTML, CSS, JS

### 2.3 Debug-Modus
- Detailliertes Logging
- Chunk-Überwachung
- Transfer-Statistiken

## 3. Performance-Optimierungen

### 3.1 Dateiübertragung
- Optimierte Chunk-Größe (4 MB)
- Effiziente Pufferung
- Automatische Flush-Steuerung

### 3.2 Caching
- Private Cache-Einstellungen
- Keine Zwischenspeicherung
- Sofortige Validierung

### 3.3 Sicherheit
- Erweiterte Header-Konfiguration
- Strikte MIME-Type-Validierung
- Rollenbasierte Zugriffskontrolle

## 4. Fehlerbehandlung

### 4.1 Debug-Modus
- Detaillierte Fehlerprotokolle
- Chunk-Überwachung
- Transfer-Statistiken

### 4.2 Fehlercodes
- 403: Unberechtigter Zugriff
- 404: Datei nicht gefunden
- 500: Server-Fehler

## 5. Wartung

### 5.1 Regelmäßige Aufgaben
- Überprüfung der Zugriffsrechte
- Validierung der MIME-Types
- Überwachung der Debug-Logs

### 5.2 Backup
- Regelmäßige Sicherung der Konfiguration
- Protokollierung der Änderungen
- Überwachung der Systemintegrität 