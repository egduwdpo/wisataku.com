<?php
require_once '../config/database.php';
session_start();
$sender = $_SESSION['user_id'];
$receiver = $_POST['receiver'];
$message = $_POST['msg'];

$stmt = $pdo->prepare("INSERT INTO Messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->execute([$sender, $receiver, $message]);
?>