#Begriffserklärungen

## Bedeutung der Begriffe ACCESS_TTL, EFRESH_TTL und CODE_TTL

Die Abkürzung TTL steht für Time-To-Live (Lebensdauer). Sie gibt im Kontext von 
Netzwerken und Protokollen wie OAuth2 das Zeitfenster an, in dem ein bestimmter 
Datensatz oder Token gültig ist, bevor er automatisch verfällt.

Im Zusammenhang mit einem OAuth2-Server haben die drei Größen folgende 
Bedeutung:
* ACCESS_TTL: Bestimmt die Lebensdauer des Access Tokens. Da diese Token oft 
  direkt vom Ressourcen-Server ohne erneute Datenbankabfrage geprüft werden, 
  ist diese Zeit meist kurz gewählt (z. B. 30 bis 60 Minuten), um das 
  Sicherheitsrisiko bei einem Diebstahl zu minimieren.
* REFRESH_TTL: Bestimmt die Lebensdauer des Refresh Tokens. Dieses Token wird 
  genutzt, um neue Access Token zu beantragen, wenn die alte ACCESS_TTL 
  abgelaufen ist. Da der Benutzer sich dadurch nicht ständig neu anmelden 
  muss, ist diese Lebensdauer deutlich länger (z. B. 30 Tage bis mehrere 
  Monate).
* CODE_TTL: Bestimmt die Lebensdauer des Authorization Codes (beim 
  Authorization Code Grant Flow). Dieser Code wird nach dem Login an den 
  Client übermittelt und muss sofort gegen ein Token-Paar eingetauscht werden. 
  Da er nur für diesen Zweck gedacht ist, ist die Zeit extrem kurz (z. B. 1 
  bis 10 Minuten).


