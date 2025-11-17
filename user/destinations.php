<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Ambil daftar kabupaten dari DB
$kabupatenList = $pdo->query("SELECT DISTINCT kabupaten FROM Destinations ORDER BY kabupaten")->fetchAll(PDO::FETCH_COLUMN);

$filter = $_GET['kabupaten'] ?? '';

// Query dengan filter
$sql = "SELECT d.*, c.nama_kategori FROM Destinations d 
        LEFT JOIN Categories c ON d.kategori_id = c.id";
if ($filter !== '') {
    $sql .= " WHERE d.kabupaten = ?";
}
$stmt = $pdo->prepare($sql);
if ($filter !== '') {
    $stmt->execute([$filter]);
} else {
    $stmt->execute();
}
$destinations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinasi Wisata Sulawesi Selatan</title>
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
            overflow-x: hidden;
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
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
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
            background-clip: text;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 600;
            margin: 0 10px;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            color: #11998e !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 80%;
        }

        /* Hero Section */
        .hero-section {
            margin-top: 80px;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            margin-bottom: 30px;
        }

        .btn-explore {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            padding: 15px 50px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 50px;
            color: white;
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-explore:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(245, 87, 108, 0.6);
        }

        /* Hero Images */
        .hero-images {
            position: relative;
            height: 500px;
        }

        .floating-card {
            position: absolute;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border: 5px solid white;
            animation: float 6s ease-in-out infinite;
        }

        .floating-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .floating-card-1 {
            width: 250px;
            height: 250px;
            top: 0;
            left: 50px;
            animation-delay: 0s;
        }

        .floating-card-2 {
            width: 200px;
            height: 200px;
            top: 100px;
            right: 80px;
            animation-delay: 1s;
        }

        .floating-card-3 {
            width: 180px;
            height: 180px;
            bottom: 50px;
            left: 120px;
            animation-delay: 2s;
        }

        .floating-card-4 {
            width: 220px;
            height: 220px;
            bottom: 80px;
            right: 50px;
            animation-delay: 1.5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(3deg); }
        }

        /* Filter Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin: -50px auto 50px;
            position: relative;
            z-index: 10;
        }

        .filter-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .form-select:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }

        .btn-filter {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 700;
            border-radius: 15px;
            color: white;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
        }

        /* Destination Cards */
        .destinations-section {
            padding: 50px 0 100px;
        }

        .section-title {
            text-align: center;
            color: white;
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 50px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        .destination-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            border: none;
        }

        .destination-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }

        .card-img-wrapper {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .destination-card:hover .card-img-wrapper img {
            transform: scale(1.15) rotate(2deg);
        }

        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        .card-body {
            padding: 25px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .card-text {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            color: #888;
            font-size: 0.9rem;
        }

        .card-meta i {
            color: #11998e;
            margin-right: 5px;
        }

        .btn-detail {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 15px;
            color: white;
            font-weight: 700;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-detail:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
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

        /* Decorative Elements */
        .floating-icon {
            position: fixed;
            font-size: 3rem;
            opacity: 0.1;
            color: white;
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }

        .icon-1 { top: 15%; left: 5%; animation-delay: 0s; }
        .icon-2 { top: 25%; right: 10%; animation-delay: 2s; }
        .icon-3 { bottom: 20%; left: 8%; animation-delay: 4s; }
        .icon-4 { bottom: 30%; right: 12%; animation-delay: 3s; }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.5rem; }
            .hero-subtitle { font-size: 1.1rem; }
            .hero-images { height: 300px; }
            .floating-card { width: 150px !important; height: 150px !important; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <!-- Floating Decorative Icons -->
    <i class="bi bi-tree floating-icon icon-1"></i>
    <i class="bi bi-flower1 floating-icon icon-2"></i>
    <i class="bi bi-water floating-icon icon-3"></i>
    <i class="bi bi-sun floating-icon icon-4"></i>

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
                        <a class="nav-link" href="#"><i class="bi bi-house-fill"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-compass-fill"></i> Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./gallery.php"><i class="bi bi-images"></i> Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./dashboard.php"><i class="bi bi-person-circle"></i> Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Jelajahi Keindahan<br>
                            <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Sulawesi Selatan</span>
                        </h1>
                        <p class="hero-subtitle">
                            <i class="bi bi-compass"></i> Temukan destinasi wisata menakjubkan dari pegunungan hingga pantai eksotis
                        </p>
                        <a href="#daftar-destinasi" class="btn btn-explore">
                            <i class="bi bi-rocket-takeoff"></i> Mulai Jelajah
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-images">
                        <div class="floating-card floating-card-1">
                            <img src="../assets/images/barru.jpeg" alt="Barru">
                        </div>
                        <div class="floating-card floating-card-2">
                            <img src="../assets/images/pinrang.jpg" alt="Pinrang">
                        </div>
                        <div class="floating-card floating-card-3">
                            <img src="../assets/images/toraja.jpeg" alt="Toraja">
                        </div>
                        <div class="floating-card floating-card-4">
                            <img src="../assets/images/toraja2.jpeg" alt="Toraja">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="container">
        <div class="filter-section" data-aos="zoom-in">
            <h3 class="filter-title text-center">
                <i class="bi bi-funnel-fill"></i> Filter Destinasi
            </h3>
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <select name="kabupaten" class="form-select">
                        <option value="">🗺️ Semua Kabupaten</option>
                        <?php foreach ($kabupatenList as $kab): ?>
                            <option value="<?= htmlspecialchars($kab) ?>" <?= ($filter === $kab) ? 'selected' : '' ?>>
                                📍 <?= htmlspecialchars($kab) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="bi bi-search"></i> Cari Destinasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Destinations Section -->
    <section class="destinations-section" id="daftar-destinasi">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">
                <i class="bi bi-stars"></i> Destinasi Pilihan
            </h2>

            <?php if ($destinations): ?>
                <div class="row g-4">
                    <?php foreach ($destinations as $index => $d): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                            <div class="destination-card">
                                <div class="card-img-wrapper">
                                    <img src="../uploads/destinations/<?= htmlspecialchars($d['foto']) ?>" 
                                         alt="<?= htmlspecialchars($d['nama_destinasi']) ?>">
                                    <span class="card-badge">
                                        <i class="bi bi-star-fill"></i> Populer
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title"><?= htmlspecialchars($d['nama_destinasi']) ?></h3>
                                    <div class="card-meta">
                                        <span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($d['kabupaten']) ?></span>
                                        <span><i class="bi bi-tag-fill"></i> <?= htmlspecialchars($d['nama_kategori'] ?? 'Umum') ?></span>
                                    </div>
                                    <p class="card-text">
                                        <?= strlen($d['deskripsi']) > 120 
                                            ? htmlspecialchars(substr($d['deskripsi'], 0, 120)) . '...' 
                                            : htmlspecialchars($d['deskripsi']) ?>
                                    </p>
                                    <a href="detail.php?id=<?= $d['id'] ?>" class="btn btn-detail">
                                        <i class="bi bi-eye-fill"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="bi bi-search"></i>
                    <h3>Tidak ada destinasi ditemukan</h3>
                    <p style="color: white; opacity: 0.8;">Coba ubah filter pencarian Anda</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../includes/buble_chat.php'; ?>
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 10px 40px rgba(0, 0, 0, 0.3)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 5px 30px rgba(0, 0, 0, 0.2)';
            }
        });
    </script>
</body>
</html>