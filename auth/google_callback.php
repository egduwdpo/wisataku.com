<?php
session_start();
require '../vendor/autoload.php';
require '../config/database.php';

$client = new Google\Client();
$client->setClientId(getenv("GOOGLE_CLIENT_ID"));
$client->setClientSecret(getenv("GOOGLE_CLIENT_SECRET"));
$client->setRedirectUri(getenv("GOOGLE_REDIRECT"));
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token);

$oauth = new Google\Service\Oauth2($client);
$userInfo = $oauth->userinfo->get();

$email = $userInfo->email;
$name  = $userInfo->name;

// cek user di database
$stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // buat user baru otomatis
    $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$name, $email, 'google_oauth']);
}

// login
$_SESSION['user_id'] = $user['id'] ?? $pdo->lastInsertId();
$_SESSION['username'] = $name;
$_SESSION['role'] = 'user';

header("Location: ../user/destinations.php");
exit;
