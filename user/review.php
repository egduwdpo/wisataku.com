<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
require_once '../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = $_POST['destination_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $ulasan = $_POST['ulasan'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? null;

    if ($destination_id && $rating && $ulasan && $user_id && $username) {
        // 🔹 Cek apakah user sudah pernah review destinasi ini
        $check = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE destination_id = ? AND user_id = ?");
        $check->execute([$destination_id, $user_id]);
        $alreadyReviewed = $check->fetchColumn();

        if ($alreadyReviewed > 0) {
            // 🔸 Kalau sudah pernah review
            echo "<script>
                alert('Kamu sudah memberikan ulasan untuk destinasi ini!');
                window.location.href = 'detail.php?id=$destination_id';
            </script>";
            exit;
        }

        // 🔹 Kalau belum pernah review, simpan ke DB
        $stmt = $pdo->prepare("
            INSERT INTO reviews (destination_id, user_id, nama_pengunjung, rating, ulasan)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$destination_id, $user_id, $username, $rating, $ulasan]);

        header("Location: detail.php?id=" . $destination_id);
        exit;
    } else {
        echo "Data belum lengkap atau user belum login!";
    }
} else {
    echo "Akses tidak valid.";
}
?>
