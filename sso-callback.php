<?php
session_start();

include 'sso-config.php';

if (!isset($_GET['code'])) {
    die('No code provided');
}

$token_endpoint = $issuer . '/protocol/openid-connect/token';
$userinfo_endpoint = $issuer . '/protocol/openid-connect/userinfo';

$ch = curl_init($token_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
    'redirect_uri' => $redirect_uri,
    'client_id' => $client_id,
    'client_secret' => $client_secret
]));
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    die('Token error: ' . json_encode($response));
}

$ch = curl_init($userinfo_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $response['access_token']
]);
$userinfo = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($userinfo['email'])) {
    die('No email in userinfo');
}

require __DIR__ . '/init.php';

$client = localAPI('GetClientsDetails', ['email' => $userinfo['email']], $adminUsername);

if (isset($client['userid'])) {
    $userid = $client['userid'];
} else {
    $create = localAPI('AddClient', [
        'firstname' => $userinfo['given_name'] ?? 'Prénom',
        'lastname' => $userinfo['family_name'] ?? 'Nom',
        'email' => $userinfo['email'],
        'password2' => bin2hex(random_bytes(8)),
        'address1' => 'N/A',
        'city' => 'N/A',
        'state' => 'N/A',
        'postcode' => '00000',
        'country' => 'FR',
        'phonenumber' => '0000000000'
    ], $adminUsername);

    if (!isset($create['clientid'])) {
        die('Erreur création client: ' . json_encode($create));
    }

    $userid = $create['clientid'];
}


// Génère un token de login
$ssoToken = localAPI('CreateSsoToken', [
    'client_id' => $userid,
], $adminUsername);

if ($ssoToken['result'] === 'success') {
    header('Location: ' . $ssoToken['redirect_url']);
    exit;
} else {
    die('Erreur génération SSO Token: ' . json_encode($ssoToken));
}
