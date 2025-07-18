<?php
define("CLIENTAREA", true);
require "init.php";

if(Auth::user()) {
    App::redirect("clientarea.php");
}
session_start();


include 'sso-config.php';

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
