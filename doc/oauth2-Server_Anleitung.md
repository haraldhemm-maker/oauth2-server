# OAuth2 Authorization Server – Anleitung

## Setup

- MySQL-Schema aus ```oauth2-schema``` importieren (erstellt DB 
  ```oauth2_server``` inkl. Beispieldaten).

- Zugangsdaten in der PHP-Datei oben konstanten (```DB_DSN```, ```DB_USER```, 
  ```DB_PASS```) anpassen.

- Benutzer anlegen: ```INSERT INTO oauth_users (username, password_hash) 
  VALUES ('harald', ?)``` – 
  mit ```password_hash('geheim', PASSWORD_DEFAULT)``` (in PHP erzeugen).

- Server z. B. starten: ```php -S localhost:8000``` im Projektverzeichnis.

## Endpunkte

| Endpunkt                 | Methode | Zweck                                          |
| ------------------------ | ------- | ---------------------------------------------- |
| ```?action=authorize```      | GET     | Autorisierungsanfrage starten (Login-Formular) |
| ```?action=authorize```      | POST    | Login → Redirect mit code + state              |
| ```?action=token```         | POST    | Token-Ausstellung (3 Grants)                   |
| ```?action=introspect```     | POST    | Token-Prüfung für Resource Server              |

## Ablauf: Authorization Code + PKCE

sequenceDiagram
    participant C as Client (Browser/App)
    participant A as Authorization Server
    C->>A: GET /authorize?response_type=code&client_id=...&redirect_uri=...&code_challenge=...&state=...
    A-->>C: Login-Formular
    C->>A: POST Login (username, password)
    A-->>C: 302 Redirect: ?code=...&state=...
    C->>A: POST /token (grant_type=authorization_code, code, code_verifier, redirect_uri)
    A-->>C: access_token, refresh_token, expires_in

PKCE: Client erzeugt verifier (43–128 Zeichen) und 
challenge = BASE64URL(SHA256(verifier)).

## Weitere Grants

- refresh_token: grant_type=refresh_token&refresh_token=... 
  (mit Rotation – altes Token wird widerrufen)

- client_credentials: grant_type=client_credentials – nur konfidentielle 
  Clients (Basic Auth oder POST-Body)

## Sicherheitsmerkmale

- Access-/Refresh-Tokens und Auth-Codes werden nur als SHA-256-Hash gespeichert
- Auth-Codes: 60 s gültig, einmalig, Race-Condition-sicher (atomares UPDATE 
  ... AND used = 0)
- Code-Wiederverwendung → Widerruf aller Client-Tokens
- PKCE (S256) ist für öffentliche Clients Pflicht (OAuth 2.1)
- state-Parameter als CSRF-Schutz erzwingen
- Refresh-Token-Rotation

## Produktions-Hinweise

- HTTPS erzwingen; session.cookie_httponly = 1, session.cookie_secure = 1
- Login/Consent durch eine echte Benutzeroberfläche ersetzen
- Rate Limiting und Audit-Logs ergänzen
- Für große Systeme: bewährte Library wie league/oauth2-server erwägen

## Vollständigen OAuth2-Authorisierungsserver:

- MySQL-Schema – Tabellen für Clients, Benutzer, Auth-Codes, Access- und 
  Refresh-Tokens inkl. Beispieldaten
- PHP-Server – PDO-basierte Implementierung mit allen Endpunkten
- Anleitung – Setup, Ablaufdiagramm und Sicherheitsübersicht
- Unterstützte Features:
    * Authorization Code Flow mit PKCE (S256) – für öffentliche Clients 
      Pflicht (OAuth 2.1)
    * Refresh Token Grant mit Rotation
    * Client Credentials Grant für Maschine-zu-Maschine-Zugriff
    * Token-Introspection (RFC 7662) für Resource Server
    * Sicherheitsmerkmale: Alle Tokens werden nur als SHA-256-Hash gespeichert, 
      Auth-Codes sind einmalig (atomar gegen Race Conditions), 
      Code-Wiederverwendung sperrt alle Client-Tokens, CSRF-Schutz via state.
      Zum Starten: Schema importieren, DB-Zugangsdaten im PHP-Code anpassen, 
      Benutzer per password_hash() anlegen und mit php -S localhost:8000 
      starten. Für Produktion solltest du HTTPS erzwingen, das simple 
      Login-Formular ersetzen und Rate Limiting ergänzen – oder alternativ 
      die etablierte Library league/oauth2-server verwenden, falls du weniger 
      Eigenbau-Pflege möchtest.


