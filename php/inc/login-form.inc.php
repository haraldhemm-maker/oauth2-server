<?php


/**
 * login-form.inc.php - Login-Formular des Authorization Servers
 *
 * Wird von handle_authorize_request() in index.php per include
 * eingebunden. Erwartet die Variable $clientName (string).
 *
 * Bewusst sehr einfach gehalten: kein CSS, nur Standard-HTML.
 */

if (!isset($clientName)) {
    // Kein direkter Aufruf über den Browser erlaubt
    http_response_code(403);
    exit('Direkter Zugriff nicht erlaubt.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Anmeldung</title>
</head>
<body>
    <h1>Anmeldung bei "<?php echo htmlspecialchars($clientName); ?>"</h1>
    <form method="post" action="?action=authorize">
        <p><input name="username" placeholder="Benutzername" required></p>
        <p><input name="password" type="password" placeholder="Passwort" required></p>
        <button type="submit">Anmelden &amp; Autorisieren</button>
    </form>
</body>
</html>
