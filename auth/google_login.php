<?php
require '../vendor/autoload.php';

$client = new Google\Client();
$client->setClientId(getenv("GOOGLE_CLIENT_ID"));
$client->setClientSecret(getenv("GOOGLE_CLIENT_SECRET"));
$client->setRedirectUri(getenv("GOOGLE_REDIRECT"));
$client->addScope("email");
$client->addScope("profile");

header("Location: " . $client->createAuthUrl());
exit;
