<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM Destinations WHERE id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();
$reviews = $pdo->prepare("SELECT r.*, u.username FROM Reviews r JOIN Users u ON r.user_id = u.id WHERE destination_id = ? AND hidden = 0 ORDER BY created_at DESC");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dest['nama_destinasi']) ?> - Detail Destinasi</title>
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
            opacity: 0.3;
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

        /* Hero Image Section */
        .hero-image-section {
            margin-top: 80px;
            padding: 30px 0;
        }

        .main-image-container {
            position: relative;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
            height: 500px;
        }

        .main-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .main-image-container:hover img {
            transform: scale(1.05);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 40px;
            color: white;
        }

        .destination-title {
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 15px;
        }

        .destination-location {
            font-size: 1.3rem;
            opacity: 0.95;
        }

        /* Info Cards */
        .info-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-title i {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 1.5rem;
        }

        /* Info Stats */
        .info-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            color: white;
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);
        }

        .stat-item i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.95;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Description */
        .description-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            text-align: justify;
        }

        /* Map Container */
        .map-container {
            border-radius: 25px;
            overflow: hidden;
            height: 400px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Review Form */
        .review-form {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(17, 153, 142, 0.3);
            color: white;
        }

        .review-form h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.3);
            transition: all 0.2s;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .star-rating label:hover {
            transform: scale(1.2);
        }

        .star-rating label.active {
            color: #fbbf24;
        }

        .review-textarea {
            width: 100%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            padding: 15px;
            font-size: 1rem;
            resize: vertical;
            min-height: 120px;
            background: rgba(255, 255, 255, 0.95);
        }

        .review-textarea:focus {
            outline: none;
            border-color: white;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        .btn-submit-review {
            background: white;
            color: #11998e;
            border: none;
            padding: 15px 40px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 20px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit-review:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
        }

        /* Reviews Section */
        .reviews-container {
            margin-top: 50px;
        }

        .review-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-left: 5px solid #11998e;
        }

        .review-card:hover {
            transform: translateX(5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .reviewer-name {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }

        .review-rating {
            color: #fbbf24;
            font-size: 1.3rem;
        }

        .review-text {
            color: #666;
            line-height: 1.7;
            font-size: 1rem;
        }

        .review-date {
            color: #999;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-reviews i {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.3;
        }

        /* Back Button */
        .btn-back {
            background: rgba(255, 255, 255, 0.95);
            color: #11998e;
            border: none;
            padding: 12px 30px;
            border-radius: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-back:hover {
            transform: translateX(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .destination-title { font-size: 2rem; }
            .destination-location { font-size: 1rem; }
            .main-image-container { height: 300px; }
            .info-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="destinations.php">
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
                        <a class="nav-link" href="destinations.php"><i class="bi bi-compass-fill"></i> Destinasi</a>
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

    <!-- Main Content -->
    <div class="container hero-image-section">
        <a href="destinations.php" class="btn-back" data-aos="fade-right">
            <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Destinasi
        </a>

        <!-- Hero Image -->
        <div class="main-image-container" data-aos="zoom-in">
            <img src="../uploads/destinations/<?= htmlspecialchars($dest['foto']) ?>" 
                 alt="<?= htmlspecialchars($dest['nama_destinasi']) ?>">
            <div class="image-overlay">
                <h1 class="destination-title"><?= htmlspecialchars($dest['nama_destinasi']) ?></h1>
                <p class="destination-location">
                    <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($dest['kabupaten']) ?>
                </p>
            </div>
        </div>

        <!-- Info Stats -->
        <div class="info-stats" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-item">
                <i class="bi bi-currency-dollar"></i>
                <div class="stat-label">Harga Tiket</div>
                <div class="stat-value">Rp <?= number_format($dest['harga_tiket']) ?></div>
            </div>
            <div class="stat-item">
                <i class="bi bi-clock-fill"></i>
                <div class="stat-label">Jam Operasional</div>
                <div class="stat-value"><?= substr($dest['jam_buka'], 0, 5) ?> - <?= substr($dest['jam_tutup'], 0, 5) ?></div>
            </div>
            <div class="stat-item">
                <i class="bi bi-star-fill"></i>
                <div class="stat-label">Rating</div>
                <div class="stat-value"><?= $dest['rating_rata2'] ?> ⭐</div>
            </div>
            <div class="stat-item">
                <i class="bi bi-chat-left-text-fill"></i>
                <div class="stat-label">Total Ulasan</div>
                <div class="stat-value"><?= $dest['total_ulasan'] ?></div>
            </div>
        </div>

        <div class="row">
            <!-- Description Section -->
            <div class="col-lg-8" data-aos="fade-right" data-aos-delay="200">
                <div class="info-card">
                    <h2 class="section-title">
                        <i class="bi bi-info-circle-fill"></i>
                        Tentang Destinasi
                    </h2>
                    <p class="description-text"><?= nl2br(htmlspecialchars($dest['deskripsi'])) ?></p>
                </div>

                <!-- Map Section -->
                <div class="info-card">
                    <h2 class="section-title">
                        <i class="bi bi-map-fill"></i>
                        Lokasi & Peta
                    </h2>
                    <div class="map-container">
                        <?= $dest['lokasi_maps'] ?>
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="review-form">
                    <h3><i class="bi bi-pencil-square"></i> Tulis Ulasan</h3>
                    <form action="review.php" method="POST">
                        <input type="hidden" name="destination_id" value="<?= $id ?>">
                        
                        <div class="star-rating" id="star-rating">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <label class="star" data-value="<?= $i ?>">⭐</label>
                            <?php endfor; ?>
                            <input type="hidden" name="rating" id="rating-value" required>
                        </div>

                        <textarea name="ulasan" 
                                  class="review-textarea" 
                                  placeholder="Bagikan pengalaman Anda di destinasi ini..." 
                                  required></textarea>

                        <button type="submit" class="btn-submit-review">
                            <i class="bi bi-send-fill"></i> Kirim Ulasan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-container" data-aos="fade-up" data-aos-delay="300">
            <div class="info-card">
                <h2 class="section-title">
                    <i class="bi bi-chat-quote-fill"></i>
                    Ulasan Pengunjung
                </h2>

                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?= strtoupper(substr($r['nama_pengunjung'] ?? $r['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="reviewer-name">
                                            <?= htmlspecialchars($r['nama_pengunjung'] ?? $r['username']) ?>
                                        </div>
                                        <div class="review-date">
                                            <i class="bi bi-calendar3"></i> 
                                            <?= date('d M Y', strtotime($r['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?= str_repeat('⭐', $r['rating']) ?>
                                </div>
                            </div>
                            <p class="review-text"><?= htmlspecialchars($r['ulasan']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="bi bi-chat-left-dots"></i>
                        <h4>Belum ada ulasan</h4>
                        <p>Jadilah yang pertama memberikan ulasan untuk destinasi ini!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Star Rating System
        const stars = document.querySelectorAll('#star-rating .star');
        const ratingInput = document.getElementById('rating-value');
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = star.dataset.value;
                ratingInput.value = rating;
                updateStars(rating);
            });
            
            star.addEventListener('mouseover', () => {
                const hoverValue = star.dataset.value;
                updateStars(hoverValue);
            });
        });

        document.getElementById('star-rating').addEventListener('mouseleave', () => {
            const rating = ratingInput.value || 0;
            updateStars(rating);
        });

        function updateStars(rating) {
            stars.forEach(s => {
                if (s.dataset.value <= rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>