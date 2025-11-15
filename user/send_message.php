<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) die();

$sender = $_SESSION['user_id'];
$receiver = 1; // Admin ID
$message = $_POST['message'];

$stmt = $pdo->prepare("INSERT INTO Messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->execute([$sender, $receiver, $message]);
?>