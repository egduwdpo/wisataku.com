<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$message = '';
$messageType = '';

if ($_POST) {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama_kategori'];
    $desk = $_POST['deskripsi'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE Categories SET nama_kategori=?, deskripsi=? WHERE id=?");
        $stmt->execute([$nama, $desk, $id]);
        $message = "Kategori berhasil diperbarui!";
        $messageType = "success";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Categories (nama_kategori, deskripsi) VALUES (?, ?)");
        $stmt->execute([$nama, $desk]);
        $message = "Kategori berhasil ditambahkan!";
        $messageType = "success";
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Kategori berhasil dihapus!";
    $messageType = "danger";
}

$categories = $pdo->query("SELECT c.*, COUNT(d.id) as total_destinations FROM Categories c LEFT JOIN Destinations d ON c.id = d.kategori_id GROUP BY c.id ORDER BY c.created_at DESC")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920') center/cover fixed;
            z-index: -1;
            opacity: 0.1;
        }

        .content-wrapper {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s;
        }

        .page-header {
            background: white;
            border-radius: 25px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .alert-custom {
            border-radius: 15px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.5s;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card {
            background: white;
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .form-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .form-card-header i {
            font-size: 2rem;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-card-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 18px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-3px);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .category-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
        }

        .category-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }

        .category-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .category-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .category-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }

        .category-count {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .category-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-edit:hover {
            transform: scale(1.2) rotate(10deg);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: white;
        }

        .btn-delete:hover {
            transform: scale(1.2) rotate(-10deg);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .empty-state i {
            font-size: 5rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #718096;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #a0aec0;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header" data-aos="fade-down">
            <h1 class="page-title">
                <i class="bi bi-tags-fill"></i> Kelola Kategori
            </h1>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Alert Message -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-custom" data-aos="fade-down">
                <i class="bi bi-<?= $messageType == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                <span><?= $message ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card" data-aos="fade-up">
            <div class="form-card-header">
                <i class="bi bi-<?= $edit ? 'pencil-square' : 'plus-circle-fill' ?>"></i>
                <h3><?= $edit ? 'Edit' : 'Tambah' ?> Kategori</h3>
            </div>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

                <div class="row g-4">
                    <!-- Nama Kategori -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-tag-fill"></i> Nama Kategori
                        </label>
                        <input type="text" name="nama_kategori" class="form-control" 
                               value="<?= htmlspecialchars($edit['nama_kategori'] ?? '') ?>" 
                               placeholder="Contoh: Pantai, Gunung, Budaya" required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-file-text-fill"></i> Deskripsi
                        </label>
                        <textarea name="deskripsi" class="form-control" 
                                  placeholder="Deskripsikan kategori ini..." required><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $edit ? 'check-circle' : 'plus-circle' ?>-fill"></i>
                            <?= $edit ? 'Update Kategori' : 'Simpan Kategori' ?>
                        </button>
                        <?php if ($edit): ?>
                            <a href="categories.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Categories Grid -->
        <?php if (count($categories) > 0): ?>
            <div class="categories-grid">
                <?php foreach ($categories as $index => $c): ?>
                    <div class="category-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="category-icon">
                            <i class="bi bi-<?= getIconForCategory($c['nama_kategori']) ?>"></i>
                        </div>
                        <h4 class="category-name"><?= htmlspecialchars($c['nama_kategori']) ?></h4>
                        <p class="category-description"><?= htmlspecialchars($c['deskripsi']) ?></p>
                        
                        <div class="category-meta">
                            <span class="category-count">
                                <i class="bi bi-geo-alt-fill"></i>
                                <?= $c['total_destinations'] ?> Destinasi
                            </span>
                            <div class="category-actions">
                                <a href="?edit=<?= $c['id'] ?>" class="btn-action btn-edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="?delete=<?= $c['id'] ?>" class="btn-action btn-delete" 
                                   onclick="return confirm('Yakin hapus kategori ini?')" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="bi bi-tags"></i>
                <h4>Belum Ada Kategori</h4>
                <p>Mulai tambahkan kategori untuk mengelompokkan destinasi wisata</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-custom');
            if (alert) {
                alert.style.animation = 'slideDown 0.5s reverse';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>

<?php
// Helper function untuk mendapatkan icon berdasarkan nama kategori
function getIconForCategory($name) {
    $name = strtolower($name);
    $iconMap = [
        'pantai' => 'water',
        'gunung' => 'mountain',
        'budaya' => 'people',
        'sejarah' => 'book',
        'kuliner' => 'cup-hot',
        'alam' => 'tree',
        'air terjun' => 'water',
        'taman' => 'flower1',
        'museum' => 'building',
        'religi' => 'bookmark-star',
        'adventure' => 'compass',
        'edukasi' => 'mortarboard'
    ];
    
    foreach ($iconMap as $keyword => $icon) {
        if (strpos($name, $keyword) !== false) {
            return $icon;
        }
    }
    
    return 'tag'; // default icon
}
?>