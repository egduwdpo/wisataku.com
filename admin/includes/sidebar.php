<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get unread messages count
require_once '../config/database.php';
$unread = $pdo->prepare("SELECT COUNT(*) FROM Messages WHERE receiver_id = ? AND is_read = 0");
$unread->execute([$_SESSION['user_id']]);
$unread_count = $unread->fetchColumn();
?>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        background: linear-gradient(180deg, #11998e 0%, #0d7a6f 100%);
        box-shadow: 5px 0 30px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        overflow-y: auto;
        transition: all 0.3s;
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }

    .sidebar-header {
        padding: 30px 25px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        background: rgba(0, 0, 0, 0.2);
    }

    .sidebar-logo {
        width: 70px;
        height: 70px;
        background: white;
        color: #11998e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: pulse 3s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .sidebar-title {
        color: white;
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    }

    .sidebar-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin: 5px 0 0 0;
    }

    .sidebar-menu {
        padding: 20px 0;
    }

    .menu-item {
        margin: 5px 15px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        border-radius: 15px;
        transition: all 0.3s;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }

    .menu-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: white;
        transform: scaleY(0);
        transition: transform 0.3s;
    }

    .menu-link:hover::before {
        transform: scaleY(1);
    }

    .menu-link:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        transform: translateX(5px);
    }

    .menu-link.active {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .menu-link.active::before {
        transform: scaleY(1);
        background: #fbbf24;
    }

    .menu-icon {
        font-size: 1.4rem;
        width: 30px;
        text-align: center;
    }

    .menu-badge {
        margin-left: auto;
        background: #ff4444;
        color: white;
        border-radius: 12px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 700;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .menu-divider {
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        margin: 15px 20px;
    }

    .logout-section {
        padding: 20px 15px;
        border-top: 2px solid rgba(255, 255, 255, 0.1);
        margin-top: auto;
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #ff4444, #cc0000);
        color: white;
        text-decoration: none;
        border-radius: 15px;
        transition: all 0.3s;
        font-weight: 700;
        box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
    }

    .logout-link:hover {
        background: linear-gradient(135deg, #cc0000, #ff4444);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 68, 68, 0.5);
        color: white;
    }

    .admin-profile {
        padding: 20px;
        background: rgba(0, 0, 0, 0.2);
        margin: 15px;
        border-radius: 15px;
        text-align: center;
    }

    .admin-avatar {
        width: 60px;
        height: 60px;
        background: white;
        color: #11998e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0 auto 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .admin-name {
        color: white;
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
    }

    .admin-role {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.85rem;
        margin: 5px 0 0 0;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>

<nav class="sidebar">
    <!-- Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="bi bi-geo-alt-fill"></i>
        </div>
        <h1 class="sidebar-title">Wisata Sulsel</h1>
        <p class="sidebar-subtitle">Admin Panel</p>
    </div>

    <!-- Admin Profile -->
    <div class="admin-profile">
        <div class="admin-avatar">
            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
        </div>
        <p class="admin-name"><?= htmlspecialchars($_SESSION['username']) ?></p>
        <p class="admin-role">
            <i class="bi bi-shield-check"></i> Administrator
        </p>
    </div>

    <!-- Menu -->
    <div class="sidebar-menu">
        <div class="menu-item">
            <a href="index.php" class="menu-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 menu-icon"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-divider"></div>

        <div class="menu-item">
            <a href="destinations.php" class="menu-link <?= $current_page == 'destinations.php' ? 'active' : '' ?>">
                <i class="bi bi-geo-alt-fill menu-icon"></i>
                <span>Destinasi</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="categories.php" class="menu-link <?= $current_page == 'categories.php' ? 'active' : '' ?>">
                <i class="bi bi-tags-fill menu-icon"></i>
                <span>Kategori</span>
            </a>
        </div>

        <div class="menu-divider"></div>

        <div class="menu-item">
            <a href="reviews.php" class="menu-link <?= $current_page == 'reviews.php' ? 'active' : '' ?>">
                <i class="bi bi-chat-left-quote-fill menu-icon"></i>
                <span>Ulasan</span>
            </a>
        </div>

        <div class="menu-item">
            <a href="gallery.php" class="menu-link <?= $current_page == 'gallery.php' ? 'active' : '' ?>">
                <i class="bi bi-images menu-icon"></i>
                <span>Galeri</span>
            </a>
        </div>

        <div class="menu-divider"></div>

        <div class="menu-item">
            <a href="chat.php" class="menu-link <?= $current_page == 'chat.php' ? 'active' : '' ?>">
                <i class="bi bi-chat-dots-fill menu-icon"></i>
                <span>Chat User</span>
                <?php if ($unread_count > 0): ?>
                    <span class="menu-badge"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Logout -->
    <div class="logout-section">
        <a href="../auth/logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right menu-icon"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<!-- Mobile Toggle Button -->
<button class="btn btn-primary d-md-none" 
        style="position: fixed; top: 20px; left: 20px; z-index: 1001; border-radius: 50%; width: 50px; height: 50px;"
        onclick="document.querySelector('.sidebar').classList.toggle('show')">
    <i class="bi bi-list"></i>
</button>