<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE Reviews SET hidden = NOT hidden WHERE id = ?");
    $stmt->execute([$id]);
    // Trigger akan otomatis update rating
}

$reviews = $pdo->query("SELECT r.*, d.nama_destinasi, u.username FROM Reviews r JOIN Destinations d ON r.destination_id = d.id JOIN Users u ON r.user_id = u.id ORDER BY r.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Ulasan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 ml-64">
    <?php include 'includes/sidebar.php'; ?>

    <div class="p-8">
        <h1 class="text-3xl font-bold mb-6">Kelola Ulasan</h1>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Destinasi</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Rating</th>
                        <th class="p-3 text-left">Ulasan</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                    <tr class="border-t">
                        <td class="p-3"><?= $r['nama_destinasi'] ?></td>
                        <td class="p-3"><?= $r['username'] ?></td>
                        <td class="p-3"><?= str_repeat('⭐', $r['rating']) ?></td>
                        <td class="p-3 max-w-xs truncate"><?= $r['ulasan'] ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs <?= $r['hidden'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                <?= $r['hidden'] ? 'Tersembunyi' : 'Tampil' ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <a href="?toggle=<?= $r['id'] ?>" class="text-blue-600">
                                <?= $r['hidden'] ? 'Tampilkan' : 'Sembunyikan' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>