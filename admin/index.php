<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$total_dest = $pdo->query("SELECT COUNT(*) FROM Destinations")->fetchColumn();
$total_user = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'user'")->fetchColumn();
$total_review = $pdo->query("SELECT COUNT(*) FROM Reviews")->fetchColumn();
$total_gallery = $pdo->query("SELECT COUNT(*) FROM Galeri")->fetchColumn();

$unread = $pdo->prepare("SELECT COUNT(*) FROM Messages WHERE receiver_id = ? AND is_read = 0");
$unread->execute([$_SESSION['user_id']]);
$unread_count = $unread->fetchColumn();

// Recent activities
$recent_reviews = $pdo->query("
    SELECT r.*, u.username, d.nama_destinasi 
    FROM Reviews r 
    JOIN Users u ON r.user_id = u.id 
    JOIN Destinations d ON r.destination_id = d.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
")->fetchAll();

$recent_users = $pdo->query("
    SELECT username, email, created_at 
    FROM Users 
    WHERE role = 'user' 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wisata Sulsel</title>
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
            opacity: 0.1;
        }

        .dashboard-container {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s;
        }

        /* Header Section */
        .dashboard-header {
            background: white;
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
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

        .stat-card.pink {
            --gradient-start: #f093fb;
            --gradient-end: #f5576c;
        }

        .stat-card.orange {
            --gradient-start: #fa709a;
            --gradient-end: #fee140;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .stat-label {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Activity Cards */
        .activity-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }

        .activity-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .activity-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f0f0f0;
        }

        .activity-header i {
            font-size: 2rem;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .activity-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .activity-item {
            padding: 15px;
            border-left: 4px solid transparent;
            margin-bottom: 10px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .activity-item:hover {
            background: #f8f9fa;
            border-left-color: #11998e;
            transform: translateX(5px);
        }

        .activity-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .activity-name {
            font-weight: 700;
            color: #333;
        }

        .activity-badge {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .activity-detail {
            color: #666;
            font-size: 0.9rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .rating-stars {
            color: #fbbf24;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: white;
            border: none;
            padding: 25px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: #333;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--btn-color-start), var(--btn-color-end));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .action-btn:hover::before {
            opacity: 1;
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .action-btn > * {
            position: relative;
            z-index: 1;
        }

        .action-btn i {
            font-size: 2.5rem;
            transition: transform 0.3s;
        }

        .action-btn:hover i {
            transform: scale(1.2) rotate(10deg);
        }

        .action-btn.teal-btn {
            --btn-color-start: #11998e;
            --btn-color-end: #38ef7d;
        }

        .action-btn.blue-btn {
            --btn-color-start: #4facfe;
            --btn-color-end: #00f2fe;
        }

        .action-btn.purple-btn {
            --btn-color-start: #a8edea;
            --btn-color-end: #fed6e3;
        }

        .action-btn.pink-btn {
            --btn-color-start: #f093fb;
            --btn-color-end: #f5576c;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                padding: 20px;
            }

            .dashboard-title {
                font-size: 2rem;
            }

            .activity-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
 <?php include 'includes/sidebar.php'; ?> 

    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header" data-aos="fade-down">
            <h1 class="dashboard-title">
                <i class="bi bi-speedometer2"></i> Dashboard Admin
            </h1>
            <p class="dashboard-subtitle">
                Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 
                | <i class="bi bi-calendar3"></i> <?= date('l, d F Y') ?>
            </p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions" data-aos="fade-up">
            <a href="destinations.php" class="action-btn teal-btn">
                <i class="bi bi-geo-alt-fill"></i>
                <span style="font-weight: 700;">Kelola Destinasi</span>
            </a>
            <a href="reviews.php" class="action-btn blue-btn">
                <i class="bi bi-chat-quote-fill"></i>
                <span style="font-weight: 700;">Kelola Ulasan</span>
            </a>
            <a href="gallery.php" class="action-btn purple-btn">
                <i class="bi bi-images"></i>
                <span style="font-weight: 700;">Kelola Galeri</span>
            </a>
            <a href="chat.php" class="action-btn pink-btn">
                <i class="bi bi-chat-dots-fill"></i>
                <span style="font-weight: 700;">Chat User</span>
                <?php if ($unread_count > 0): ?>
                    <span style="position: absolute; top: 10px; right: 10px; background: #ff4444; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card teal" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="stat-label">Total Destinasi</div>
                <div class="stat-value"><?= $total_dest ?></div>
            </div>

            <div class="stat-card blue" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-label">Total Pengguna</div>
                <div class="stat-value"><?= $total_user ?></div>
            </div>

            <div class="stat-card purple" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon">
                    <i class="bi bi-chat-left-quote-fill"></i>
                </div>
                <div class="stat-label">Total Ulasan</div>
                <div class="stat-value"><?= $total_review ?></div>
            </div>

            <div class="stat-card pink" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-icon">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <div class="stat-label">Foto di Galeri</div>
                <div class="stat-value"><?= $total_gallery ?></div>
            </div>

            <div class="stat-card orange" data-aos="fade-up" data-aos-delay="500">
                <div class="stat-icon">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div class="stat-label">Pesan Belum Dibaca</div>
                <div class="stat-value"><?= $unread_count ?></div>
                <?php if ($unread_count > 0): ?>
                    <span class="stat-badge"><?= $unread_count ?> Baru</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Section -->
        <div class="activity-section">
            <!-- Recent Reviews -->
            <div class="activity-card" data-aos="fade-right">
                <div class="activity-header">
                    <i class="bi bi-star-fill"></i>
                    <h3>Ulasan Terbaru</h3>
                </div>
                <?php if ($recent_reviews): ?>
                    <?php foreach ($recent_reviews as $review): ?>
                        <div class="activity-item">
                            <div class="activity-item-header">
                                <span class="activity-name"><?= htmlspecialchars($review['username']) ?></span>
                                <span class="activity-badge">
                                    <?= str_repeat('⭐', $review['rating']) ?>
                                </span>
                            </div>
                            <div class="activity-detail">
                                <strong><?= htmlspecialchars($review['nama_destinasi']) ?></strong>
                            </div>
                            <div class="activity-detail">
                                "<?= htmlspecialchars(substr($review['ulasan'], 0, 60)) ?>..."
                            </div>
                            <div class="activity-time">
                                <i class="bi bi-clock"></i> <?= date('d M Y, H:i', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-4">Belum ada ulasan</p>
                <?php endif; ?>
            </div>

            <!-- Recent Users -->
            <div class="activity-card" data-aos="fade-left">
                <div class="activity-header">
                    <i class="bi bi-person-plus-fill"></i>
                    <h3>Pengguna Baru</h3>
                </div>
                <?php if ($recent_users): ?>
                    <?php foreach ($recent_users as $user): ?>
                        <div class="activity-item">
                            <div class="activity-item-header">
                                <span class="activity-name">
                                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['username']) ?>
                                </span>
                                <span class="activity-badge">Baru</span>
                            </div>
                            <div class="activity-detail">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                            </div>
                            <div class="activity-time">
                                <i class="bi bi-clock"></i> Bergabung <?= date('d M Y', strtotime($user['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted py-4">Belum ada pengguna baru</p>
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
    </script>
</body>
</html>