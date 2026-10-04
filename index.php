<?php

/**
 * OAuth 2.1 Authorization Server (PHP + MySQL/PDO)
 *
 * @author Harald Hemm <hemm@nexgo.de>
 * @copyright 2026 Harald Hemm Landstuhl (HaHeLa)
 * @license CC BY-NC-ND HaHeLa
 * @version 1.0.0
 * @since 1.0.0
 *
 * Endpunkte:
 *   GET  /authorize    - Authorization Endpoint
 *                          (Authorization Code Flow + PKCE)
 *   POST /authorize    - Login + Zustimmung, stellt Code aus
 *   POST /token        - Token Endpoint (authorization_code,
 *                          refresh_token, client_credentials)
 *   POST /introspect   - Token-Prüfung für Resource Server
 *
 * Sicherheit: Tokens werden nur als SHA-256-Hash gespeichert,
 * Auth-Codes sind einmalig und 60 s gültig, PKCE (S256) Pflicht
 *  für öffentliche Clients.
 * 
 * Beispiel [2026-10-02] - Anmeldung MIT und OHNE '?action=authorize'
 * Bash (Server starten):
 *  cd ~/public_html/app/oauth2-server/
 *  php -S localhost:8000
 * Browser (Anmeldeformular anzeigen):
 *  http://localhost:8000/index.php?action=authorize&response_type=code&client_id=demo-app&redirect_uri=http://localhost:3000/callback&state=xyz123&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URrbu3JyHTYKf4&code_challenge_method=S256
 *  http://localhost:8000/index.php?response_type=code&client_id=demo-app&redirect_uri=http://localhost:3000/callback&state=xyz123&code_challenge=E9Melhoa2OwvFrEMTJguCHaoeK1t8URrbu3JyHTYKf4&code_challenge_method=S256
 */

declare(strict_types=1);

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

session_start();


/* ---------------------------------------------------------------------------
 * Konfiguration laden
 * ------------------------------------------------------------------------ */
$globConfig = array();
$globConfig = parse_ini_file(
    'dat'.DS
    .'configuration-'
        .strtolower(gethostname())
        .'-'.strtolower(php_uname('s'))
        .'.ini', 
    true
);


/* ---------------------------------------------------------------------------
 * PDO-Verbindung
 * ------------------------------------------------------------------------ */
include_once 'php'.DS.'inc'.DS.'database.inc.php';


/* ---------------------------------------------------------
 * HTTP-Helfer
 * --------------------------------------------------------- */
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    header('Pragma: no-cache');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function oauth_error(string $error, string $desc, int $status = 400): never
{
    json_response(['error' => $error, 'error_description' => $desc], $status);
}

function random_token(): string
{
    return bin2hex(random_bytes(32));
}

function hash_token(string $token): string
{
    return hash('sha256', $token);
}

function client_from_request(): ?array
{
    // 1. Basic Auth, 2. POST-Body
    $clientId = $_SERVER['PHP_AUTH_USER'] ?? ($_POST['client_id'] ?? null);
    $secret   = $_SERVER['PHP_AUTH_PW']  ?? ($_POST['client_secret'] ?? null);
    if (!$clientId) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM oauth_clients WHERE client_id = ?');
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();
    if (!$client) {
        return null;
    }
    if ((int)$client['is_confidential'] === 1) {
        if (!$secret || !hash_equals($client['client_secret'], hash('sha256', $secret))) {
            return null;
        }
    }
    return $client;
}

/* ---------------------------------------------------------
 * Token-Ausstellung
 * --------------------------------------------------------- */
function issue_tokens(string $clientId, ?int $userId, string $scope): array
{
    global $globConfig;
    $accessToken  = random_token();
    $refreshToken = random_token();

    $stmt = db()->prepare(
        'INSERT INTO oauth_access_tokens (token, client_id, user_id, scope, expires_at)
         VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
    );
    $stmt->execute(
        [
            hash_token($accessToken), 
            $clientId, 
            $userId, 
            $scope, 
            $globConfig['TTL']['ACCESS']
        ]
    );

    $stmt = db()->prepare(
        'INSERT INTO oauth_refresh_tokens (token, client_id, user_id, scope, expires_at)
         VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
    );
    $stmt->execute(
        [
            hash_token($refreshToken), 
            $clientId, 
            $userId, 
            $scope, 
            $globConfig['TTL']['REFRESH']
        ]
    );

    return [
        'access_token'  => $accessToken,
        'refresh_token' => $refreshToken,
        'token_type'    => 'Bearer',
        'expires_in'    => $globConfig['TTL']['ACCESS'],
        'scope'         => $scope,
    ];
}

/* =========================================================
 * GET /authorize
 * ========================================================= */
function handle_authorize_request(): void
{
    $responseType = $_GET['response_type'] ?? '';
    $clientId     = $_GET['client_id'] ?? '';
    $redirectUri  = $_GET['redirect_uri'] ?? '';
    $scope        = trim($_GET['scope'] ?? '');
    $state        = $_GET['state'] ?? '';
    $challenge    = $_GET['code_challenge'] ?? '';
    $method       = strtoupper($_GET['code_challenge_method'] ?? '');

    if ($responseType !== 'code') {
        oauth_error('unsupported_response_type', 'Nur response_type=code wird unterstützt.');
    }
    if ($state === '') {
        oauth_error('invalid_request', 'state ist erforderlich (CSRF-Schutz).');
    }

    $stmt = db()->prepare('SELECT * FROM oauth_clients WHERE client_id = ?');
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();
    if (!$client || $client['redirect_uri'] !== $redirectUri) {
        oauth_error('invalid_request', 'client_id oder redirect_uri ungültig.');
    }

    // PKCE für öffentliche Clients verpflichtend (OAuth 2.1)
    if ((int)$client['is_confidential'] === 0) {
        if ($challenge === '' || !in_array($method, ['S256', 'PLAIN'], true)) {
            oauth_error('invalid_request', 'PKCE mit code_challenge (S256) ist für öffentliche Clients Pflicht.');
        }
        if ($method === 'PLAIN' && strlen($challenge) < 43) {
            oauth_error('invalid_request', 'code_challenge zu kurz.');
        }
    }

    $_SESSION['oauth2'] = [
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'scope'         => $scope,
        'state'         => $state,
        'code_challenge' => $challenge,
        'challenge_method' => $method,
    ];

    // Sehr einfaches Login-Formular (in Produktion durch echtes Login ersetzen)
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="de"><meta charset="utf-8"><title>Anmeldung</title>
    <h1>Anmeldung bei "' . htmlspecialchars($client['client_name']) . '"</h1>
    <form method="post" action="?action=authorize">
      <p><input name="username" placeholder="Benutzername" required></p>
      <p><input name="password" type="password" placeholder="Passwort" required></p>
      <button type="submit">Anmelden &amp; Autorisieren</button>
    </form>';
    exit;
}

/* =========================================================
 * POST /authorize (Login + Consent -> Code-Ausstellung)
 * ========================================================= */
function handle_authorize_submit(): void
{
    global $globConfig;
    $sess = $_SESSION['oauth2'] ?? null;
    if (!$sess) {
        oauth_error('invalid_request', 'Autorisierungssitzung abgelaufen, bitte neu starten.');
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM oauth_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        header('Content-Type: text/html; charset=utf-8');
        echo '<p>Ungültige Zugangsdaten. <a href="javascript:history.back()">Zurück</a></p>';
        exit;
    }

    // Authorization Code erzeugen
    $code = random_token();
    $stmt = db()->prepare(
        'INSERT INTO oauth_auth_codes
           (code, client_id, user_id, redirect_uri, scope, code_challenge, code_challenge_method, expires_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
    );
    $stmt->execute(
        [
            hash_token($code), $sess['client_id'], $user['id'],
            $sess['redirect_uri'], $sess['scope'],
            $sess['code_challenge'] ?: null, $sess['challenge_method'] ?: null,
            $globConfig['TTL']['CODE'],
        ]
    );
    unset($_SESSION['oauth2']);

    $params = http_build_query([
        'code'  => $code,
        'state' => $sess['state'],
    ]);
    header('Location: ' . $sess['redirect_uri'] . (str_contains($sess['redirect_uri'], '?') ? '&' : '?') . $params);
    exit;
}

/* =========================================================
 * POST /token
 * ========================================================= */
function handle_token_request(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        oauth_error('invalid_request', 'POST erforderlich.', 405);
    }
    $grantType = $_POST['grant_type'] ?? '';

    switch ($grantType) {
        case 'authorization_code':
            grant_authorization_code();
            break;
        case 'refresh_token':
            grant_refresh_token();
            break;
        case 'client_credentials':
            grant_client_credentials();
            break;
        default:
            oauth_error('unsupported_grant_type', 'Unbekannter grant_type.');
    }
}

function grant_authorization_code(): never
{
    $client = client_from_request();
    if (!$client) {
        // 401 mit WWW-Authenticate Header
        header('WWW-Authenticate: Basic realm="oauth"');
        oauth_error('invalid_client', 'Client-Authentifizierung fehlgeschlagen.', 401);
    }

    $code        = $_POST['code'] ?? '';
    $redirectUri = $_POST['redirect_uri'] ?? '';
    $verifier   = $_POST['code_verifier'] ?? '';

    if ($code === '' || $redirectUri === '') {
        oauth_error('invalid_request', 'code und redirect_uri sind erforderlich.');
    }

    $stmt = db()->prepare('SELECT * FROM oauth_auth_codes WHERE code = ?');
    $stmt->execute([hash_token($code)]);
    $authCode = $stmt->fetch();

    $now = new DateTimeImmutable();
    if (!$authCode) {
        oauth_error('invalid_grant', 'Authorization Code ungültig.');
    }
    if ((int)$authCode['used'] === 1) {
        // Code-Wiederverwendung -> Tokens dieses Clients widerrufen
        db()->prepare('UPDATE oauth_access_tokens SET revoked = 1 WHERE client_id = ?')
            ->execute([$client['client_id']]);
        oauth_error('invalid_grant', 'Authorization Code wurde bereits verwendet.');
    }
    if (new DateTimeImmutable($authCode['expires_at']) < $now) {
        oauth_error('invalid_grant', 'Authorization Code abgelaufen.');
    }
    if ($authCode['client_id'] !== $client['client_id'] || $authCode['redirect_uri'] !== $redirectUri) {
        oauth_error('invalid_grant', 'client_id oder redirect_uri stimmen nicht überein.');
    }

    // PKCE prüfen
    if ($authCode['code_challenge'] !== null) {
        $method = $authCode['code_challenge_method'];
        $expected = $method === 'S256'
            ? rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=')
            : $verifier;
        if ($verifier === '' || !hash_equals($authCode['code_challenge'], $expected)) {
            oauth_error('invalid_grant', 'PKCE-Verifikation fehlgeschlagen.');
        }
    }

    // Code als verbraucht markieren (atomar, gegen Race Conditions)
    $stmt = db()->prepare('UPDATE oauth_auth_codes SET used = 1 WHERE id = ? AND used = 0');
    $stmt->execute([$authCode['id']]);
    if ($stmt->rowCount() !== 1) {
        oauth_error('invalid_grant', 'Authorization Code wurde bereits verwendet.');
    }

    json_response(issue_tokens($client['client_id'], (int)$authCode['user_id'], $authCode['scope']));
}

function grant_refresh_token(): never
{
    $client = client_from_request();
    if (!$client) {
        oauth_error('invalid_client', 'Client-Authentifizierung fehlgeschlagen.', 401);
    }

    $refreshToken = $_POST['refresh_token'] ?? '';
    if ($refreshToken === '') {
        oauth_error('invalid_request', 'refresh_token erforderlich.');
    }

    $stmt = db()->prepare('SELECT * FROM oauth_refresh_tokens WHERE token = ?');
    $stmt->execute([hash_token($refreshToken)]);
    $rt = $stmt->fetch();

    if (!$rt || (int)$rt['revoked'] === 1 || $rt['client_id'] !== $client['client_id']
        || new DateTimeImmutable($rt['expires_at']) < new DateTimeImmutable()) {
        oauth_error('invalid_grant', 'Refresh Token ungültig, abgelaufen oder widerrufen.');
    }

    // Rotation: altes Refresh Token widerrufen, neue Paarung ausstellen
    db()->prepare('UPDATE oauth_refresh_tokens SET revoked = 1 WHERE id = ?')->execute([$rt['id']]);
    json_response(issue_tokens($rt['client_id'], $rt['user_id'] !== null ? (int)$rt['user_id'] : null, $rt['scope']));
}

function grant_client_credentials(): never
{
    $client = client_from_request();
    if (!$client) {
        oauth_error('invalid_client', 'Client-Authentifizierung fehlgeschlagen.', 401);
    }
    if ((int)$client['is_confidential'] !== 1) {
        oauth_error('unauthorized_client', 'Nur konfidentielle Clients dürfen client_credentials nutzen.');
    }
    $scope = trim($_POST['scope'] ?? '');
    // user_id = NULL: Token gehört dem Client selbst
    $tokens = issue_tokens($client['client_id'], null, $scope);
    unset($tokens['refresh_token']); // Client-Credentials: kein Refresh Token
    json_response($tokens);
}

/* =========================================================
 * POST /introspect (RFC 7662) - für Resource Server
 * ========================================================= */
function handle_introspect(): void
{
    $token = $_POST['token'] ?? '';
    $stmt = db()->prepare(
        'SELECT * FROM oauth_access_tokens WHERE token = ?'
    );
    $stmt->execute([hash_token($token)]);
    $tok = $stmt->fetch();

    if (!$tok || (int)$tok['revoked'] === 1
        || new DateTimeImmutable($tok['expires_at']) < new DateTimeImmutable()) {
        json_response(['active' => false]);
    }
    json_response([
        'active'    => true,
        'scope'     => $tok['scope'],
        'client_id' => $tok['client_id'],
        'user_id'   => $tok['user_id'],
        'exp'       => (new DateTimeImmutable($tok['expires_at']))->getTimestamp(),
    ]);
}

/* =========================================================
 * Routing
 * ========================================================= */
$action = $_GET['action'] ?? 'authorize';

switch ($action) {
    case 'authorize':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            handle_authorize_request();
        } else {
            handle_authorize_submit();
        }
        break;
    case 'token':
        handle_token_request();
        break;
    case 'introspect':
        handle_introspect();
        break;
    default:
        oauth_error('invalid_request', 'Unbekannter Endpunkt.', 404);
}
