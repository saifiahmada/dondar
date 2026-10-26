<?php
session_start();

/*
|--------------------------------------------------------------------------
| Konfigurasi database
|--------------------------------------------------------------------------
| Sesuaikan username dan password dengan konfigurasi MariaDB/MySQL Anda.
*/
$dbHost = '127.0.0.1';
$dbName = 'db_dondar_polanka';
$dbUser = 'root';
$dbPass = '';

$errors = [];
$old = [
    'nama_lengkap' => '',
    'alamat' => '',
    'tanggal_lahir' => '',
    'jenis_kelamin' => '',
    'golongan_darah' => '',
    'no_hp' => '',
];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['flash_success'])) {
    $flashSuccess = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
} else {
    $flashSuccess = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Sesi formulir tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.';
    }

    if ($old['nama_lengkap'] === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    } elseif (mb_strlen($old['nama_lengkap']) > 100) {
        $errors[] = 'Nama lengkap maksimal 100 karakter.';
    }

    if (mb_strlen($old['alamat']) > 5000) {
        $errors[] = 'Alamat terlalu panjang.';
    }

    if ($old['tanggal_lahir'] === '') {
        $errors[] = 'Tanggal lahir wajib diisi.';
    } else {
        $date = DateTime::createFromFormat('!Y-m-d', $old['tanggal_lahir']);
        $dateErrors = DateTime::getLastErrors();
        $validDate = $date
            && $date->format('Y-m-d') === $old['tanggal_lahir']
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));

        if (!$validDate) {
            $errors[] = 'Tanggal lahir tidak valid.';
        } elseif ($old['tanggal_lahir'] > date('Y-m-d')) {
            $errors[] = 'Tanggal lahir tidak boleh melebihi tanggal hari ini.';
        }
    }

    if (!in_array($old['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = 'Silakan pilih jenis kelamin.';
    }

    $golonganDarahValid = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    if (!in_array($old['golongan_darah'], $golonganDarahValid, true)) {
        $errors[] = 'Silakan pilih golongan darah yang valid.';
    }

    if ($old['no_hp'] !== '') {
        if (mb_strlen($old['no_hp']) > 20) {
            $errors[] = 'Nomor HP maksimal 20 karakter.';
        } elseif (!preg_match('/^[0-9+().\-\s]+$/', $old['no_hp'])) {
            $errors[] = 'Nomor HP hanya boleh berisi angka, spasi, dan karakter + ( ) - .';
        }
    }

    if (!$errors) {
        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $sql = "INSERT INTO pendonor
                        (nama_lengkap, alamat, tanggal_lahir, jenis_kelamin, golongan_darah, no_hp)
                    VALUES
                        (:nama_lengkap, :alamat, :tanggal_lahir, :jenis_kelamin, :golongan_darah, :no_hp)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama_lengkap' => $old['nama_lengkap'],
                ':alamat' => $old['alamat'] !== '' ? $old['alamat'] : null,
                ':tanggal_lahir' => $old['tanggal_lahir'],
                ':jenis_kelamin' => $old['jenis_kelamin'],
                ':golongan_darah' => $old['golongan_darah'],
                ':no_hp' => $old['no_hp'] !== '' ? $old['no_hp'] : null,
            ]);

            $_SESSION['flash_success'] = 'Data pendonor berhasil disimpan.';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } catch (PDOException $e) {
            // Detail error disimpan di log server, tidak ditampilkan ke pengguna.
            error_log('Gagal menyimpan data pendonor: ' . $e->getMessage());
            $errors[] = 'Data belum berhasil disimpan. Periksa koneksi database atau hubungi administrator.';
        }
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Pendonor | Donor Darah Kampus</title>

    <!-- AdminLTE 3, Bootstrap 4, dan Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .required-mark { color: #dc3545; }
        .form-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #343a40;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: .65rem;
            margin-bottom: 1.25rem;
        }
        .form-control:focus, .custom-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 .2rem rgba(0,123,255,.15);
        }
        .content-header h1 { font-size: 1.6rem; }
        .help-text { font-size: .85rem; color: #6c757d; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Buka atau tutup menu">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link"><i class="fas fa-heartbeat text-danger mr-1"></i> Donor Darah Kampus</span>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="pendonor_create.php" class="brand-link">
            <i class="fas fa-heartbeat ml-3 mr-2"></i>
            <span class="brand-text font-weight-light">Donor Darah</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-light"></i>
                </div>
                <div class="info">
                    <span class="d-block text-white">Panel Petugas</span>
                </div>
            </div>

            <nav class="mt-2" aria-label="Navigasi utama">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="pendonor_create.php" class="nav-link active">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Tambah Pendonor</p>
                        </a>
                    </li>
                    <!-- Tambahkan tautan daftar pendonor atau kegiatan setelah halamannya tersedia. -->
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Konten utama -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1>Tambah Data Pendonor</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="pendonor_create.php">Beranda</a></li>
                            <li class="breadcrumb-item active">Tambah Pendonor</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                <?php if ($flashSuccess !== ''): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <i class="fas fa-check-circle mr-2"></i><?= e($flashSuccess) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger" role="alert">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Periksa kembali data berikut</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-9 col-xl-8">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-id-card mr-2"></i>Formulir Identitas Pendonor
                                </h3>
                            </div>

                            <form method="post" action="" autocomplete="on">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                                <div class="card-body">
                                    <p class="text-muted">
                                        Lengkapi identitas pendonor sesuai dokumen atau informasi yang telah diverifikasi.
                                        Kolom bertanda <span class="required-mark">*</span> wajib diisi.
                                    </p>

                                    <h5 class="form-section-title">
                                        <i class="fas fa-user text-primary mr-2"></i>Identitas Pendonor
                                    </h5>

                                    <div class="form-group">
                                        <label for="nama_lengkap">Nama Lengkap <span class="required-mark">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nama_lengkap"
                                            name="nama_lengkap"
                                            maxlength="100"
                                            value="<?= e($old['nama_lengkap']) ?>"
                                            placeholder="Masukkan nama lengkap"
                                            autocomplete="name"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="tanggal_lahir">Tanggal Lahir <span class="required-mark">*</span></label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="tanggal_lahir"
                                            name="tanggal_lahir"
                                            max="<?= date('Y-m-d') ?>"
                                            value="<?= e($old['tanggal_lahir']) ?>"
                                            required>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="jenis_kelamin">Jenis Kelamin <span class="required-mark">*</span></label>
                                            <select class="custom-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="">-- Pilih jenis kelamin --</option>
                                                <option value="L" <?= $old['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                <option value="P" <?= $old['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="golongan_darah">Golongan Darah <span class="required-mark">*</span></label>
                                            <select class="custom-select" id="golongan_darah" name="golongan_darah" required>
                                                <option value="">-- Pilih golongan darah --</option>
                                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $golongan): ?>
                                                    <option value="<?= e($golongan) ?>" <?= $old['golongan_darah'] === $golongan ? 'selected' : '' ?>>
                                                        <?= e($golongan) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <h5 class="form-section-title mt-4">
                                        <i class="fas fa-address-book text-primary mr-2"></i>Informasi Kontak
                                    </h5>

                                    <div class="form-group">
                                        <label for="alamat">Alamat Lengkap</label>
                                        <textarea
                                            class="form-control"
                                            id="alamat"
                                            name="alamat"
                                            rows="3"
                                            placeholder="Nama jalan, kelurahan/desa, kecamatan, dan kota"
                                            autocomplete="street-address"><?= e($old['alamat']) ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="no_hp">Nomor HP</label>
                                        <input
                                            type="tel"
                                            class="form-control"
                                            id="no_hp"
                                            name="no_hp"
                                            maxlength="20"
                                            inputmode="tel"
                                            value="<?= e($old['no_hp']) ?>"
                                            placeholder="Contoh: 081234567890"
                                            autocomplete="tel"
                                            aria-describedby="no_hp_help">
                                        <small id="no_hp_help" class="help-text">
                                            Masukkan nomor yang aktif. Nomor HP tidak wajib diisi.
                                        </small>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-between flex-wrap">
                                    <button type="reset" class="btn btn-default mb-2 mb-sm-0">
                                        <i class="fas fa-undo mr-1"></i> Reset Form
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Simpan Pendonor
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Setelah identitas tersimpan, hasil pemeriksaan dan kelayakan donor dicatat pada formulir pemeriksaan terpisah.
                        </div>
                    </div>

                    <div class="col-lg-3 col-xl-4">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Petunjuk Pengisian</h3>
                            </div>
                            <div class="card-body">
                                <ul class="pl-3 mb-0">
                                    <li class="mb-2">Pastikan nama pendonor ditulis dengan benar.</li>
                                    <li class="mb-2">Periksa kembali tanggal lahir sebelum menyimpan.</li>
                                    <li class="mb-2">Pilih golongan darah sesuai informasi yang telah diverifikasi.</li>
                                    <li class="mb-2">Gunakan nomor HP aktif jika pendonor bersedia memberikannya.</li>
                                    <li>Jaga kerahasiaan identitas pendonor.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="small text-muted">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Data hanya boleh diakses oleh petugas yang berwenang.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Donor Darah Kampus</strong>
        <div class="float-right d-none d-sm-inline-block">Sistem Pencatatan Donor Darah</div>
    </footer>
</div>

<!-- JavaScript AdminLTE 3 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>