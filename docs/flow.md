# Ablauf eines Dateiabrufs

## Prozessdiagramm

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

## Erklärung der Schritte

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

## Sicherheitsaspekte

- Keine direkte URL-Zugänglichkeit
- Rollenbasierte Zugriffskontrolle
- Pfadvalidierung gegen Directory Traversal
- Sichere Dateiübertragung
- Keine PHP-Ausführung in geschützten Verzeichnissen 