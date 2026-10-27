# Funktionsweise der Registrierung beim OAuth2-Server

* GET ?action=register 
    zeigt das Registrierungsformular.
* POST ?action=register 
    validiert (Benutzername 3–64 Zeichen, nur A-Z a-z 0-9 . _ -; 

Passwort mind. 8 Zeichen; Wiederholung muss stimmen; Benutzername eindeutig) 
und legt den Benutzer mit password_hash(..., PASSWORD_DEFAULT) an.

Läuft die Registrierung innerhalb eines Autorisierungs-Flows (Nutzer kam vom 
Login der web-app), werden die OAuth-Parameter aus der Session mitgenommen: 
Nach der Registrierung kann der Nutzer per „Jetzt anmelden" direkt zum Login 
des ursprünglichen Clients zurückkehren.

Fehler (z. B. vergebener Name) zeigt das Formular erneut an, mit Fehlermeldung 
und erhaltenem Benutzernamen.

Kein automatisch angemeldeter Zustand: Der neue Benutzer muss sich danach 
regulär anmelden – das ist der saubere OAuth-Weg (kein Login über Umwege).

Test nach systemctl --user restart oauth2-server:
http://localhost:8000/index.php?action=register öffnen → Benutzer anlegen 
    → dann über die web-app „anmelden" → mit den neuen Zugangsdaten 
    einloggen.
