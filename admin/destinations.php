<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$message = '';
$messageType = '';

// Create / Update
if ($_POST) {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama_destinasi'];
    $kab = $_POST['kabupaten'];
    $kat = $_POST['kategori_id'];
    $desk = $_POST['deskripsi'];
    $fas = $_POST['fasilitas'];
    $harga = $_POST['harga_tiket'];
    $buka = $_POST['jam_buka'];
    $tutup = $_POST['jam_tutup'];
    $maps = $_POST['lokasi_maps'];
    $fotoLama = $_POST['foto_lama'] ?? null;
    $galleryLama = $_POST['gallery_lama'] ?? '[]';

    $folderTujuan = "../uploads/destinations/";
    if (!is_dir($folderTujuan)) mkdir($folderTujuan, 0777, true);

    // Upload foto utama
    $namaFile = $fotoLama;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $namaFile = time() . "_main_" . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $folderTujuan . $namaFile);
    }

    // Upload gallery photos (multiple)
    $galleryArray = json_decode($galleryLama, true) ?? [];
    
    if (isset($_FILES['foto_gallery']) && is_array($_FILES['foto_gallery']['tmp_name'])) {
        foreach ($_FILES['foto_gallery']['tmp_name'] as $key => $tmp) {
            if ($_FILES['foto_gallery']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = time() . "_gallery_" . $key . "_" . basename($_FILES['foto_gallery']['name'][$key]);
                if (move_uploaded_file($tmp, $folderTujuan . $filename)) {
                    $galleryArray[] = $filename;
                }
            }
        }
    }

    // Convert gallery array to JSON
    $foto_gallery_json = json_encode($galleryArray);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE Destinations 
            SET nama_destinasi=?, kabupaten=?, kategori_id=?, deskripsi=?, fasilitas=?, harga_tiket=?, 
                jam_buka=?, jam_tutup=?, foto=?, lokasi_maps=?, foto_gallery=? 
            WHERE id=?");
        $stmt->execute([$nama, $kab, $kat, $desk, $fas, $harga, $buka, $tutup, $namaFile, $maps, $foto_gallery_json, $id]);
        $message = "Destinasi berhasil diperbarui!";
        $messageType = "success";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Destinations 
            (nama_destinasi, kabupaten, kategori_id, deskripsi, fasilitas, harga_tiket, 
             jam_buka, jam_tutup, foto, lokasi_maps, foto_gallery) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$nama, $kab, $kat, $desk, $fas, $harga, $buka, $tutup, $namaFile, $maps, $foto_gallery_json]);
        $message = "Destinasi berhasil ditambahkan!";
        $messageType = "success";
    }
}

// Delete Gallery Photo
if (isset($_GET['delete_gallery']) && isset($_GET['dest_id'])) {
    $dest_id = $_GET['dest_id'];
    $photo_name = $_GET['delete_gallery'];
    
    // Get current gallery
    $stmt = $pdo->prepare("SELECT foto_gallery FROM Destinations WHERE id = ?");
    $stmt->execute([$dest_id]);
    $dest = $stmt->fetch();
    
    $gallery = json_decode($dest['foto_gallery'], true) ?? [];
    
    // Remove photo from array
    $gallery = array_values(array_filter($gallery, function($photo) use ($photo_name) {
        return $photo !== $photo_name;
    }));
    
    // Delete file
    $file_path = "../uploads/destinations/" . $photo_name;
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE Destinations SET foto_gallery = ? WHERE id = ?");
    $stmt->execute([json_encode($gallery), $dest_id]);
    
    header("Location: destinations.php?edit=" . $dest_id . "&gallery_deleted=1");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Destinations WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Destinasi berhasil dihapus!";
    $messageType = "danger";
}

$destinations = $pdo->query("SELECT d.*, c.nama_kategori FROM Destinations d LEFT JOIN Categories c ON d.kategori_id = c.id ORDER BY d.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM Categories")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Destinations WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Destinasi - Admin</title>
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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

        .alert-custom i {
            font-size: 1.5rem;
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 18px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 40px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
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

        .table-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f0f0;
        }

        .table-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 45px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: #11998e;
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            font-weight: 700;
            border: none;
            padding: 15px;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .destination-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .badge-category {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-price {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .rating-badge {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .action-buttons {
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

        .file-input-wrapper {
            position: relative;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border: 2px dashed #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            border-color: #11998e;
            background: #f8f9fa;
        }

        .file-input-label input[type="file"] {
            display: none;
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

            .search-box {
                width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
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
                <i class="bi bi-geo-alt-fill"></i> Kelola Destinasi
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
                <h3><?= $edit ? 'Edit' : 'Tambah' ?> Destinasi</h3>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                <input type="hidden" name="foto_lama" value="<?= $edit['foto'] ?? '' ?>">
                <input type="hidden" name="gallery_lama" value="<?= htmlspecialchars($edit['foto_gallery'] ?? '[]') ?>">

                <div class="row g-4">
                    <!-- Nama Destinasi -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-pin-map-fill"></i> Nama Destinasi
                        </label>
                        <input type="text" name="nama_destinasi" class="form-control" 
                               value="<?= $edit['nama_destinasi'] ?? '' ?>" 
                               placeholder="Contoh: Pantai Losari" required>
                    </div>

                    <!-- Kabupaten -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-building"></i> Kabupaten
                        </label>
                        <input type="text" name="kabupaten" class="form-control" 
                               value="<?= $edit['kabupaten'] ?? '' ?>" 
                               placeholder="Contoh: Makassar" required>
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-tags-fill"></i> Kategori
                        </label>
                        <select name="kategori_id" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($edit && $edit['kategori_id'] == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Harga Tiket -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-currency-dollar"></i> Harga Tiket (Rp)
                        </label>
                        <input type="number" step="0.01" name="harga_tiket" class="form-control" 
                               value="<?= $edit['harga_tiket'] ?? '' ?>" 
                               placeholder="Contoh: 50000" required>
                    </div>

                    <!-- Jam Buka -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-clock"></i> Jam Buka
                        </label>
                        <input type="time" name="jam_buka" class="form-control" 
                               value="<?= $edit['jam_buka'] ?? '' ?>" required>
                    </div>

                    <!-- Jam Tutup -->
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-clock-fill"></i> Jam Tutup
                        </label>
                        <input type="time" name="jam_tutup" class="form-control" 
                               value="<?= $edit['jam_tutup'] ?? '' ?>" required>
                    </div>

                    <!-- Foto -->
                    <div class="col-12">
                        <label class="form-label">
                            <i class="bi bi-image-fill"></i> Foto Utama Destinasi
                        </label>
                        <div class="file-input-wrapper">
                            <label class="file-input-label">
                                <i class="bi bi-cloud-upload-fill" style="font-size: 1.5rem;"></i>
                                <span id="file-name"><?= $edit ? 'Ganti Foto (Opsional)' : 'Pilih Foto Utama' ?></span>
                                <input type="file" name="foto" accept="image/*" 
                                       <?= $edit ? '' : 'required' ?> 
                                       onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Pilih Foto'">
                            </label>
                        </div>
                        <?php if ($edit && $edit['foto']): ?>
                            <img src="../uploads/destinations/<?= $edit['foto'] ?>" 
                                 class="mt-3 destination-img" alt="Current">
                        <?php endif; ?>
                    </div>

                    <!-- Foto Gallery (Multiple) -->
                    <div class="col-12">
                        <label class="form-label">
                            <i class="bi bi-images"></i> Gallery Foto (Opsional - Max 5 foto)
                        </label>
                        <div class="file-input-wrapper">
                            <label class="file-input-label">
                                <i class="bi bi-cloud-upload-fill" style="font-size: 1.5rem;"></i>
                                <span id="gallery-file-name">Pilih Multiple Foto</span>
                                <input type="file" name="foto_gallery[]" accept="image/*" multiple 
                                       onchange="updateGalleryFileName(this)">
                            </label>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Anda bisa pilih hingga 5 foto sekaligus (Ctrl+Click atau Cmd+Click)
                        </small>
                        
                        <?php if ($edit && !empty($edit['foto_gallery'])): ?>
                            <?php 
                            $gallery = json_decode($edit['foto_gallery'], true) ?? [];
                            if (!empty($gallery)):
                            ?>
                                <div class="mt-3">
                                    <strong>Foto Gallery Saat Ini:</strong>
                                    <div class="gallery-preview mt-2">
                                        <?php foreach ($gallery as $photo): ?>
                                            <div class="gallery-item">
                                                <img src="../uploads/destinations/<?= htmlspecialchars($photo) ?>" alt="Gallery">
                                                <a href="?delete_gallery=<?= urlencode($photo) ?>&dest_id=<?= $edit['id'] ?>" 
                                                   class="delete-gallery-btn"
                                                   onclick="return confirm('Hapus foto ini dari gallery?')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12">
                        <label class="form-label">
                            <i class="bi bi-file-text-fill"></i> Deskripsi
                        </label>
                        <textarea name="deskripsi" class="form-control" rows="4" 
                                  placeholder="Deskripsikan destinasi..." required><?= $edit['deskripsi'] ?? '' ?></textarea>
                    </div>

                    <!-- Fasilitas -->
                    <div class="col-12">
                        <label class="form-label">
                            <i class="bi bi-list-check"></i> Fasilitas
                        </label>
                        <textarea name="fasilitas" class="form-control" rows="3" 
                                  placeholder="Contoh: Parkir, Toilet, Mushola, Warung"><?= $edit['fasilitas'] ?? '' ?></textarea>
                    </div>

                    <!-- Google Maps -->
                    <div class="col-12">
                        <label class="form-label">
                            <i class="bi bi-map-fill"></i> Embed Google Maps (iframe)
                        </label>
                        <textarea name="lokasi_maps" class="form-control" rows="4" 
                                  placeholder='<iframe src="..." ...></iframe>' required><?= $edit['lokasi_maps'] ?? '' ?></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $edit ? 'check-circle' : 'plus-circle' ?>-fill"></i>
                            <?= $edit ? 'Update Destinasi' : 'Simpan Destinasi' ?>
                        </button>
                        <?php if ($edit): ?>
                            <a href="destinations.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card" data-aos="fade-up" data-aos-delay="200">
            <div class="table-header">
                <h3><i class="bi bi-table"></i> Daftar Destinasi</h3>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari destinasi..." 
                           onkeyup="searchTable()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" id="destinationTable">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Destinasi</th>
                            <th>Kabupaten</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Rating</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $d): ?>
                        <tr>
                            <td>
                                <img src="../uploads/destinations/<?= htmlspecialchars($d['foto']) ?>" 
                                     class="destination-img" alt="<?= htmlspecialchars($d['nama_destinasi']) ?>">
                            </td>
                            <td><strong><?= htmlspecialchars($d['nama_destinasi']) ?></strong></td>
                            <td><?= htmlspecialchars($d['kabupaten']) ?></td>
                            <td>
                                <span class="badge-category">
                                    <?= htmlspecialchars($d['nama_kategori'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-price">
                                    Rp <?= number_format($d['harga_tiket'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td>
                                <span class="rating-badge">
                                    <?= $d['rating_rata2'] ?> ⭐
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?= $d['id'] ?>" class="btn-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="?delete=<?= $d['id'] ?>" class="btn-action btn-delete" 
                                       onclick="return confirm('Yakin hapus destinasi ini?')" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

        // Search function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('destinationTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }

        // Auto hide alert after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-custom');
            if (alert) {
                alert.style.animation = 'slideDown 0.5s reverse';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);

        // Update gallery file name display
        function updateGalleryFileName(input) {
            const fileNameDisplay = document.getElementById('gallery-file-name');
            if (input.files.length > 0) {
                if (input.files.length === 1) {
                    fileNameDisplay.textContent = input.files[0].name;
                } else {
                    fileNameDisplay.textContent = `${input.files.length} foto dipilih`;
                }
            } else {
                fileNameDisplay.textContent = 'Pilih Multiple Foto';
            }
        }
    </script>
</body>
</html>