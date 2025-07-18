<?php
session_start();

require "init.php";

$sessionCookieValue = null;
foreach ($_COOKIE as $name => $value) {
    if (strpos($name, 'WHMCS3SL') === 0) {
        $sessionCookieValue = $value;
        break;
    }
}

if (!$sessionCookieValue) {
    $isConnected = false;
} else {
    $response = localAPI('ValidateSession', ['sessionkey' => $sessionCookieValue]);

    if (isset($response['result']) && $response['result'] === 'success' && !empty($response['userid'])) {
        $isConnected = true;
        $userid = $response['userid'];
    } else {
        $isConnected = false;
    }
}

if ($isConnected) {
    header('Location: clientarea.php');
    exit;
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
