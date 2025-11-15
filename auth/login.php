<?php
require_once '../config/database.php';
session_start();

$error = '';
$success = '';

// Handle Register
if (isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = password_hash($_POST['reg_password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$username, $email, $password]);
        $success = "Pendaftaran berhasil! Silakan login.";
    } catch (PDOException $e) {
        $error = "Username atau email sudah digunakan!";
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['login_email']);
    $password = $_POST['login_password'];

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../user/destinations.php");
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata Alam - Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920') center/cover no-repeat fixed;
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
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(3px);
        }

        .auth-container {
            position: relative;
            z-index: 1;
            padding: 40px 0;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-section {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.95) 0%, rgba(56, 239, 125, 0.95) 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800') center/cover;
            opacity: 0.2;
        }
        
        .header-section > * {
            position: relative;
            z-index: 1;
        }

        .header-section i {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .nav-tabs {
            border: none;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 15px;
            margin: 30px 20px 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #11998e;
            background: white;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }

        .tab-content {
            padding: 30px 40px 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group-text {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
            font-size: 1.2rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .nature-icons {
            position: absolute;
            font-size: 2rem;
            opacity: 0.1;
            animation: float 4s ease-in-out infinite;
        }

        .icon-1 { top: 10%; left: 5%; animation-delay: 0s; }
        .icon-2 { top: 20%; right: 8%; animation-delay: 1s; }
        .icon-3 { bottom: 15%; left: 10%; animation-delay: 2s; }
        .icon-4 { bottom: 25%; right: 5%; animation-delay: 1.5s; }

        @media (max-width: 768px) {
            .header-section h1 { font-size: 2rem; }
            .tab-content { padding: 20px; }
        }
    </style>
</head>
<body>
    <!-- Nature Icons Background -->
    <i class="bi bi-tree nature-icons icon-1"></i>
    <i class="bi bi-flower1 nature-icons icon-2"></i>
    <i class="bi bi-water nature-icons icon-3"></i>
    <i class="bi bi-sun nature-icons icon-4"></i>

    <div class="container auth-container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card">
                    <!-- Header -->
                    <div class="header-section">
                        <i class="bi bi-geo-alt-fill"></i>
                        <h1>Wisata Alam</h1>
                        <p class="d-flex align-items-center gap-3">
                        <i class="bi bi-compass fs-1 me-2"></i>
                            <span class="fs-3">Jelajahi Keindahan Alam Sulawesi</span>
                        </p>

                    </div>

                    <!-- Alert Messages -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger mx-4 mt-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success mx-4 mt-3" role="alert">
                            <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="authTabs" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link active w-100" id="login-tab" data-bs-toggle="tab" 
                                    data-bs-target="#login" type="button" role="tab">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link w-100" id="register-tab" data-bs-toggle="tab" 
                                    data-bs-target="#register" type="button" role="tab">
                                <i class="bi bi-person-plus-fill"></i> Register
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="authTabContent">
                        <!-- Login Form -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <form method="POST">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" name="login_email" class="form-control" 
                                           placeholder="Email" required>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" name="login_password" class="form-control" 
                                           placeholder="Password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="login" class="btn btn-primary btn-lg">
                                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Register Form -->
                        <div class="tab-pane fade" id="register" role="tabpanel">
                            <form method="POST">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <input type="text" name="reg_username" class="form-control" 
                                           placeholder="Username" required>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="email" name="reg_email" class="form-control" 
                                           placeholder="Email" required>
                                </div>

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" name="reg_password" class="form-control" 
                                           placeholder="Password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="register" class="btn btn-primary btn-lg">
                                        <i class="bi bi-person-plus-fill"></i> Daftar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto switch to login tab if registration successful
        <?php if ($success): ?>
            var loginTab = new bootstrap.Tab(document.getElementById('login-tab'));
            loginTab.show();
        <?php endif; ?>
    </script>
</body>
</html>