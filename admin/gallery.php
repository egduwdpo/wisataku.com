<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_foto FROM Galeri WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file && file_exists("../uploads/gallery/$file")) unlink("../uploads/gallery/$file");

    $stmt = $pdo->prepare("DELETE FROM Galeri WHERE id = ?");
    $stmt->execute([$id]);
}

$galeri = $pdo->query("SELECT g.*, u.username FROM Galeri g LEFT JOIN Users u ON g.nama_pengunggah = u.username ORDER BY tanggal_upload DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Galeri</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 ml-64">
    <?php include 'includes/sidebar.php'; ?>

    <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Kelola Galeri</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($galeri as $g): ?>
                <div class="relative group">
                    <img src="../uploads/gallery/<?= $g['file_foto'] ?>" class="w-full h-48 object-cover rounded-lg shadow">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <a href="?delete=<?= $g['id'] ?>" onclick="return confirm('Hapus foto ini?')" class="bg-red-600 text-white px-4 py-2 rounded">Hapus</a>
                    </div>
                    <p class="text-xs mt-1 text-center">by <?= $g['username'] ?? $g['nama_pengunggah'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>