<?php
session_start();

require "init.php";

foreach ($_COOKIE as $cookieName => $cookieValue) {
    if (strpos($cookieName, 'WHMCS') === 0 && !empty($cookieValue)) {
        header('Location: /clientarea.php');
        exit;
    }
}

$issuer = 'ton issuer url';
$client_id = 'le client id';
$redirect_uri = 'https://ton-whmcs.com/sso-callback.php';

$_SESSION['sso_flow'] = 'login';

$authorize_url = $issuer . '/protocol/openid-connect/auth?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => 'openid email profile',
    'prompt' => 'login'
]);

header('Location: ' . $authorize_url);
exit;
