<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_POST && isLoggedIn()) {
    $file = $_FILES['foto'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], "../uploads/gallery/$filename");
    $stmt = $pdo->prepare("INSERT INTO Galeri (nama_pengunggah, judul_foto, file_foto) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['username'], $_POST['judul'], $filename]);
    header("Location: gallery.php?success=1");
    exit;
}

$galeri = $pdo->query("SELECT * FROM Galeri ORDER BY tanggal_upload DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Wisata - Sulawesi Selatan</title>
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
            position: relative;
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
            opacity: 0.2;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.2);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #11998e !important;
            transform: translateY(-2px);
        }

        /* Container */
        .gallery-container {
            margin-top: 100px;
            padding-bottom: 80px;
        }

        /* Header Section */
        .gallery-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .gallery-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 15px;
        }

        .gallery-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
        }

        /* Upload Section */
        .upload-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(245, 87, 108, 0.4);
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
        }

        .upload-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            animation: rotate 20s infinite linear;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .upload-card > * {
            position: relative;
            z-index: 1;
        }

        .upload-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .upload-title i {
            font-size: 2.5rem;
        }

        .form-control {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
            background: white;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.95);
            border: 3px dashed rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .file-input-label:hover {
            background: white;
            border-color: white;
            transform: translateY(-2px);
        }

        .btn-upload {
            background: white;
            color: #f5576c;
            border: none;
            padding: 15px 50px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-upload:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        /* Gallery Grid */
        .gallery-grid {
            column-count: 4;
            column-gap: 25px;
        }

        @media (max-width: 1200px) {
            .gallery-grid { column-count: 3; }
        }

        @media (max-width: 768px) {
            .gallery-grid { column-count: 2; }
        }

        @media (max-width: 576px) {
            .gallery-grid { column-count: 1; }
        }

        /* Photo Card */
        .photo-card {
            break-inside: avoid;
            margin-bottom: 25px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .photo-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .photo-wrapper {
            position: relative;
            overflow: hidden;
        }

        .photo-wrapper img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }

        .photo-card:hover .photo-wrapper img {
            transform: scale(1.1);
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }

        .photo-card:hover .photo-overlay {
            opacity: 1;
        }

        .photo-actions {
            display: flex;
            gap: 10px;
        }

        .photo-action-btn {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #11998e;
            font-size: 1.2rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .photo-action-btn:hover {
            background: #11998e;
            color: white;
            transform: scale(1.2);
        }

        .photo-info {
            padding: 20px;
        }

        .photo-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .photo-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #999;
            font-size: 0.9rem;
        }

        .photo-meta i {
            color: #11998e;
        }

        .photographer-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Success Alert */
        .success-alert {
            position: fixed;
            top: 100px;
            right: 30px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.4);
            z-index: 9999;
            animation: slideIn 0.5s, slideOut 0.5s 2.5s;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-state i {
            font-size: 6rem;
            color: white;
            opacity: 0.5;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        /* Floating Icons */
        .floating-icon {
            position: fixed;
            font-size: 3rem;
            opacity: 0.08;
            color: white;
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }

        .icon-1 { top: 15%; left: 5%; animation-delay: 0s; }
        .icon-2 { top: 25%; right: 10%; animation-delay: 2s; }
        .icon-3 { bottom: 20%; left: 8%; animation-delay: 4s; }
        .icon-4 { bottom: 30%; right: 12%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        @media (max-width: 768px) {
            .gallery-title { font-size: 2.5rem; }
            .upload-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <!-- Floating Icons -->
    <i class="bi bi-camera floating-icon icon-1"></i>
    <i class="bi bi-images floating-icon icon-2"></i>
    <i class="bi bi-palette floating-icon icon-3"></i>
    <i class="bi bi-stars floating-icon icon-4"></i>

    <!-- Success Alert -->
    <?php if (isset($_GET['success'])): ?>
        <div class="success-alert">
            <i class="bi bi-check-circle-fill"></i> Foto berhasil diupload!
        </div>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-geo-alt-fill"></i> Wisata Sulsel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="destinations.php"><i class="bi bi-compass-fill"></i> Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-images"></i> Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container gallery-container">
        <!-- Header -->
        <div class="gallery-header" data-aos="fade-down">
            <h1 class="gallery-title">
                <i class="bi bi-camera-fill"></i> Galeri Wisata
            </h1>
            <p class="gallery-subtitle">
                Bagikan momen indah perjalanan wisata Anda
            </p>
        </div>

        <!-- Upload Section -->
        <?php if (isLoggedIn()): ?>
            <div class="upload-card" data-aos="zoom-in">
                <h3 class="upload-title">
                    <i class="bi bi-cloud-upload-fill"></i>
                    Upload Foto Kenangan
                </h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="text" 
                               name="judul" 
                               class="form-control" 
                               placeholder="📝 Judul Foto (contoh: Sunset di Pantai Losari)" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="file-input-wrapper">
                            <input type="file" 
                                   name="foto" 
                                   id="file-input" 
                                   accept="image/*" 
                                   required>
                            <label for="file-input" class="file-input-label">
                                <i class="bi bi-image-fill" style="font-size: 1.5rem;"></i>
                                <span id="file-name">Pilih Foto (JPG, PNG, GIF)</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-upload w-100">
                        <i class="bi bi-send-fill"></i> Upload Sekarang
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Gallery Grid -->
        <?php if ($galeri): ?>
            <div class="gallery-grid">
                <?php foreach ($galeri as $index => $g): ?>
                    <div class="photo-card" data-aos="fade-up" data-aos-delay="<?= ($index % 4) * 100 ?>">
                        <div class="photo-wrapper">
                            <img src="../uploads/gallery/<?= htmlspecialchars($g['file_foto']) ?>" 
                                 alt="<?= htmlspecialchars($g['judul_foto']) ?>">
                            <div class="photo-overlay">
                                <div class="photo-actions">
                                    <div class="photo-action-btn" title="Like">
                                        <i class="bi bi-heart-fill"></i>
                                    </div>
                                    <div class="photo-action-btn" title="Download">
                                        <i class="bi bi-download"></i>
                                    </div>
                                    <div class="photo-action-btn" title="Share">
                                        <i class="bi bi-share-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="photo-info">
                            <h4 class="photo-title"><?= htmlspecialchars($g['judul_foto']) ?></h4>
                            <div class="photo-meta">
                                <span class="photographer-badge">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($g['nama_pengunggah']) ?>
                                </span>
                            </div>
                            <div class="photo-meta mt-2">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d M Y', strtotime($g['tanggal_upload'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="bi bi-images"></i>
                <h3>Galeri Masih Kosong</h3>
                <p style="color: white; opacity: 0.8;">Jadilah yang pertama mengupload foto!</p>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // File input handler
        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                }
            });
        }

        // Auto hide success alert
        setTimeout(() => {
            const alert = document.querySelector('.success-alert');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 3000);
    </script>
</body>
</html>