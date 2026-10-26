<?php
// actions/pendonor_store.php
// Dipanggil oleh index.php SEBELUM layout/HTML dirender.

require_once __DIR__ . '/../config/database.php';

$fields = [
    'nama_lengkap',
    'alamat',
    'tanggal_lahir',
    'jenis_kelamin',
    'golongan_darah',
    'no_hp',
];

$old = [];
foreach ($fields as $field) {
    $old[$field] = trim((string) ($_POST[$field] ?? ''));
}

$errors = [];

// Validasi token CSRF.
$postedToken = (string) ($_POST['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

if ($sessionToken === '' || $postedToken === '' ||
    !hash_equals($sessionToken, $postedToken)) {
    $errors[] = 'Sesi formulir tidak valid atau telah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
}

// Validasi nama.
if ($old['nama_lengkap'] === '') {
    $errors[] = 'Nama lengkap wajib diisi.';
} elseif (mb_strlen($old['nama_lengkap']) > 100) {
    $errors[] = 'Nama lengkap maksimal 100 karakter.';
}

// Validasi alamat.
if (mb_strlen($old['alamat']) > 5000) {
    $errors[] = 'Alamat maksimal 5.000 karakter.';
}

// Validasi tanggal lahir, termasuk format kalender dan tanggal masa depan.
if ($old['tanggal_lahir'] === '') {
    $errors[] = 'Tanggal lahir wajib diisi.';
} else {
    $date = DateTime::createFromFormat('!Y-m-d', $old['tanggal_lahir']);
    $dateErrors = DateTime::getLastErrors();

    $validDate = $date
        && $date->format('Y-m-d') === $old['tanggal_lahir']
        && ($dateErrors === false ||
            ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0));

    if (!$validDate) {
        $errors[] = 'Tanggal lahir tidak valid.';
    } elseif ($old['tanggal_lahir'] > date('Y-m-d')) {
        $errors[] = 'Tanggal lahir tidak boleh melebihi tanggal hari ini.';
    }
}

// Validasi pilihan jenis kelamin.
if (!in_array($old['jenis_kelamin'], ['L', 'P'], true)) {
    $errors[] = 'Silakan pilih jenis kelamin yang valid.';
}

// Validasi golongan darah.
$golonganDarahValid = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
if (!in_array($old['golongan_darah'], $golonganDarahValid, true)) {
    $errors[] = 'Silakan pilih golongan darah yang valid.';
}

// Nomor HP opsional, tetapi jika diisi harus berformat wajar.
if ($old['no_hp'] !== '') {
    if (strlen($old['no_hp']) > 20) {
        $errors[] = 'Nomor HP maksimal 20 karakter.';
    } elseif (!preg_match('/^[0-9+().\-\s]+$/', $old['no_hp'])) {
        $errors[] = 'Nomor HP hanya boleh berisi angka, spasi, dan karakter + ( ) - .';
    } elseif (!preg_match('/[0-9]/', $old['no_hp'])) {
        $errors[] = 'Nomor HP harus mengandung angka.';
    }
}

if ($errors) {
    // Simpan pesan dan input lama untuk ditampilkan kembali oleh halaman form.
    $_SESSION['pendonor_errors'] = $errors;
    $_SESSION['pendonor_old'] = $old;

    header('Location: index.php?page=pendonor-create');
    exit;
}

try {
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
    unset($_SESSION['pendonor_errors'], $_SESSION['pendonor_old']);

    // Token baru setelah proses berhasil.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: index.php?page=pendonor-create');
    exit;
} catch (PDOException $e) {
    error_log('Gagal menyimpan data pendonor: ' . $e->getMessage());

    $_SESSION['pendonor_errors'] = [
        'Data belum berhasil disimpan. Periksa koneksi database atau hubungi administrator.'
    ];
    $_SESSION['pendonor_old'] = $old;

    header('Location: index.php?page=pendonor-create');
    exit;
}
