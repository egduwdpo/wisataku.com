<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
redirectIfNotLoggedIn();

if (isAdmin()) {
    header("Location: ../admin/index.php");
    exit;
}

// Ambil data user
$user = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

// Hitung ulasan user
$reviews = $pdo->prepare("SELECT COUNT(*) FROM Reviews WHERE user_id = ?");
$reviews->execute([$_SESSION['user_id']]);
$review_count = $reviews->fetchColumn();

// Hitung foto di galeri
$photos = $pdo->prepare("SELECT COUNT(*) FROM Galeri WHERE nama_pengunggah = ?");
$photos->execute([$_SESSION['username']]);
$photo_count = $photos->fetchColumn();

// Total destinasi
$total_destinations = $pdo->query("SELECT COUNT(*) FROM Destinations")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengunjung - Wisata Sulsel</title>
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
        .dashboard-container {
            margin-top: 100px;
            padding-bottom: 80px;
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 30px;
            padding: 50px 40px;
            text-align: center;
            color: white;
            box-shadow: 0 20px 60px rgba(17, 153, 142, 0.4);
            position: relative;
            overflow: hidden;
            margin-bottom: 50px;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: wave 15s infinite linear;
        }

        @keyframes wave {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .welcome-card > * {
            position: relative;
            z-index: 1;
        }

        .welcome-avatar {
            width: 120px;
            height: 120px;
            background: white;
            color: #11998e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: white;
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        }

        .stat-card.teal {
            --gradient-start: #11998e;
            --gradient-end: #38ef7d;
        }

        .stat-card.blue {
            --gradient-start: #4facfe;
            --gradient-end: #00f2fe;
        }

        .stat-card.purple {
            --gradient-start: #a8edea;
            --gradient-end: #fed6e3;
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .stat-label {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Quick Actions */
        .section-title {
            text-align: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 40px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .action-card {
            background: white;
            border-radius: 25px;
            padding: 40px 30px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--action-color-start), var(--action-color-end));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .action-card:hover::before {
            opacity: 1;
        }

        .action-card:hover {
            transform: translateY(-15px) scale(1.05);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            color: white;
        }

        .action-card > * {
            position: relative;
            z-index: 1;
        }

        .action-card.teal-action {
            --action-color-start: #11998e;
            --action-color-end: #38ef7d;
        }

        .action-card.blue-action {
            --action-color-start: #4facfe;
            --action-color-end: #00f2fe;
        }

        .action-card.pink-action {
            --action-color-start: #f093fb;
            --action-color-end: #f5576c;
        }

        .action-card.red-action {
            --action-color-start: #fa709a;
            --action-color-end: #fee140;
        }

        .action-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .action-card:hover .action-icon {
            transform: scale(1.2) rotate(10deg);
        }

        .action-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
        }

        .action-description {
            font-size: 0.9rem;
            margin-top: 10px;
            opacity: 0.8;
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
            .welcome-title { font-size: 2rem; }
            .section-title { font-size: 2rem; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Floating Icons -->
    <i class="bi bi-tree floating-icon icon-1"></i>
    <i class="bi bi-compass floating-icon icon-2"></i>
    <i class="bi bi-camera floating-icon icon-3"></i>
    <i class="bi bi-star floating-icon icon-4"></i>

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
                        <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="destinations.php"><i class="bi bi-compass-fill"></i> Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php"><i class="bi bi-images"></i> Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container dashboard-container">
        <!-- Welcome Card -->
        <div class="welcome-card" data-aos="zoom-in">
            <div class="welcome-avatar">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <h1 class="welcome-title">
                <i class="bi bi-stars"></i> Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!
            </h1>
            <p class="welcome-subtitle">
                <i class="bi bi-compass"></i> Siap menjelajahi keindahan Sulawesi Selatan hari ini?
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card teal" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon">
                    <i class="bi bi-chat-left-quote-fill"></i>
                </div>
                <div class="stat-label">Total Ulasan Anda</div>
                <div class="stat-value"><?= $review_count ?></div>
            </div>

            <div class="stat-card blue" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div class="stat-label">Foto di Galeri</div>
                <div class="stat-value"><?= $photo_count ?></div>
            </div>

            <div class="stat-card purple" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="stat-label">Destinasi Tersedia</div>
                <div class="stat-value"><?= $total_destinations ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="section-title" data-aos="fade-up">
            <i class="bi bi-lightning-charge-fill"></i> Aksi Cepat
        </h2>

        <div class="actions-grid">
            <a href="destinations.php" class="action-card teal-action" data-aos="flip-left" data-aos-delay="100">
                <div class="action-icon">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <h3 class="action-title">Jelajah Destinasi</h3>
                <p class="action-description">Temukan tempat wisata menakjubkan</p>
            </a>

            <a href="gallery.php" class="action-card blue-action" data-aos="flip-left" data-aos-delay="200">
                <div class="action-icon">
                    <i class="bi bi-images"></i>
                </div>
                <h3 class="action-title">Galeri Foto</h3>
                <p class="action-description">Upload dan lihat foto perjalanan</p>
            </a>

            <a href="chat.php" class="action-card pink-action" data-aos="flip-left" data-aos-delay="300">
                <div class="action-icon">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <h3 class="action-title">Chat Admin</h3>
                <p class="action-description">Butuh bantuan? Hubungi kami</p>
            </a>

            <a href="../auth/logout.php" class="action-card red-action" data-aos="flip-left" data-aos-delay="400">
                <div class="action-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h3 class="action-title">Keluar</h3>
                <p class="action-description">Logout dari akun Anda</p>
            </a>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Add greeting based on time
        const hour = new Date().getHours();
        const greetingIcon = document.querySelector('.welcome-subtitle i');
        
        if (hour < 12) {
            greetingIcon.className = 'bi bi-sunrise-fill';
        } else if (hour < 18) {
            greetingIcon.className = 'bi bi-sun-fill';
        } else {
            greetingIcon.className = 'bi bi-moon-stars-fill';
        }
    </script>
</body>
</html>