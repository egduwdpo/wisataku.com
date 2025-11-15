<?php
require_once '../config/database.php';
session_start();
$receiver = $_GET['user'];
$sender = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT m.*, u.username FROM Messages m JOIN Users u ON m.sender_id = u.id WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp");
$stmt->execute([$sender, $receiver, $receiver, $sender]);
$msgs = $stmt->fetchAll();

foreach ($msgs as $m): ?>
    <div class="mb-3 <?= $m['sender_id'] == $_SESSION['user_id'] ? 'text-right' : '' ?>">
        <div class="inline-block max-w-xs p-3 rounded-lg <?= $m['sender_id'] == $_SESSION['user_id'] ? 'bg-teal-600 text-white' : 'bg-gray-200' ?>">
            <p class="text-xs font-bold"><?= $m['username'] ?></p>
            <p><?= nl2br(htmlspecialchars($m['message'])) ?></p>
            <p class="text-xs opacity-75"><?= date('H:i', strtotime($m['timestamp'])) ?></p>
        </div>
    </div>
<?php endforeach; ?>