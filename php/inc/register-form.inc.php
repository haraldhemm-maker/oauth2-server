<?php


/**
 * register-form.inc.php - Registrierungsformular des Authorization Servers
 *
 * Wird von handle_register() in index.php per include eingebunden.
 * Erwartet optional $registerError (string) für Fehlermeldungen und
 * $registerOld (array) mit den zuletzt eingegebenen Werten.
 *
 * Bewusst sehr einfach gehalten: kein CSS, nur Standard-HTML.
 */


if (!isset($registerError)) {
    // Kein direkter Aufruf über den Browser erlaubt
    http_response_code(403);
    exit('Direkter Zugriff nicht erlaubt.');
}


header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="de">
    <head>
        <meta charset="utf-8" />
        <title>Registrierung</title>
    </head>
    <body>
        <h1>Benutzerregistrierung</h1>
        <?php if ($registerError !== ''): ?>
            <p><strong><?php echo htmlspecialchars($registerError); ?></strong></p>
        <?php endif; ?>
        <form method="post" action="?action=register">
            <p><input name="username" placeholder="Benutzername" required
                    value="<?php echo htmlspecialchars($registerOld['username'] ?? ''); ?>"></p>
            <p><input name="password" type="password" placeholder="Passwort" required></p>
            <p><input name="password_confirm" type="password" placeholder="Passwort (Wiederholung)" required></p>
            <button type="submit">Registrieren</button>
        </form>
        <p><a href="?action=authorize<?php
            // Autorisierungsparameter mitnehmen, damit der Nutzer danach
            // direkt zum Login des ursprünglichen Clients zurückkehren kann
            echo isset($registerOld['authorize_query']) ? '&' . htmlspecialchars($registerOld['authorize_query']) : '';
                ?>">Zurück zur Anmeldung</a></p>
    </body>
</html>
