# Datenbanktabelle hahela_oauth2_server.oauth_clients

Einen neuen Eintrag in der Tabelle ```oauth_clients``` erzeugen:

```
INSERT INTO oauth_clients (client_id, client_secret, client_name, redirect_uri, is_confidential)
VALUES ('mein-neuer-client', NULL, 'Meine neue App', 'http://localhost/~harald/app/mein-client/php/callback.php', 0);
```

## Erzeugung des ```client_secret```

php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"

## Speicherung in die Tabelle nur als SHA-256-Hash

```
INSERT INTO oauth_clients (client_id, client_secret, client_name, redirect_uri, is_confidential)
VALUES ('mein-client', SHA2('streng-geheim', 256), 'Mein Client', 'http://…/callback', 1);
```

'streng_geheim' ist das Ergebnis des obigen PHP-Befehls.

## Speicherung durch Anwednugn von PHP-Code

```
$secret = bin2hex(random_bytes(32));          // Klartext – nur einmalig ausgeben!
$hash   = hash('sha256', $secret);            // das kommt in die DB
// INSERT ... VALUES ('mein-client', $hash, ...)

// Klartext einmalig anzeigen (wie bei GitHub-Tokens), danach nie wieder
echo $secret;
```

