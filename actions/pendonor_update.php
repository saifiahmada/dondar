
<?php
// actions/pendonor_update.php
// Memperbarui data pendonor yang sudah ada.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=pendonor-index');
    exit;
}

$idPendonor = filter_input(INPUT_POST, 'id_pendonor', FILTER_VALIDATE_INT);

if (!$idPendonor || $idPendonor < 1) {
    $_SESSION['pendonor_errors'] = ['ID pendonor tidak valid.'];
    header('Location: index.php?page=pendonor-index');
    exit;
}

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

// Validasi CSRF
$postedToken = (string) ($_POST['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

if (
    $sessionToken === '' ||
    $postedToken === '' ||
    !hash_equals($sessionToken, $postedToken)
) {
    $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
}

// Validasi nama
if ($old['nama_lengkap'] === '') {
    $errors[] = 'Nama lengkap wajib diisi.';
} elseif (mb_strlen($old['nama_lengkap']) > 100) {
    $errors[] = 'Nama lengkap maksimal 100 karakter.';
}

// Validasi alamat
if (mb_strlen($old['alamat']) > 5000) {
    $errors[] = 'Alamat maksimal 5.000 karakter.';
}

// Validasi tanggal lahir
if ($old['tanggal_lahir'] === '') {
    $errors[] = 'Tanggal lahir wajib diisi.';
} else {
    $date = DateTime::createFromFormat('!Y-m-d', $old['tanggal_lahir']);
    $dateErrors = DateTime::getLastErrors();

    $validDate = $date
        && $date->format('Y-m-d') === $old['tanggal_lahir']
        && (
            $dateErrors === false ||
            (
                $dateErrors['warning_count'] === 0 &&
                $dateErrors['error_count'] === 0
            )
        );

    if (!$validDate) {
        $errors[] = 'Tanggal lahir tidak valid.';
    } elseif ($old['tanggal_lahir'] > date('Y-m-d')) {
        $errors[] = 'Tanggal lahir tidak boleh melebihi hari ini.';
    }
}

// Validasi jenis kelamin
if (!in_array($old['jenis_kelamin'], ['L', 'P'], true)) {
    $errors[] = 'Silakan pilih jenis kelamin yang valid.';
}

// Validasi golongan darah
$golonganDarahValid = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

if (!in_array($old['golongan_darah'], $golonganDarahValid, true)) {
    $errors[] = 'Silakan pilih golongan darah yang valid.';
}

// Validasi nomor HP
if ($old['no_hp'] !== '') {
    if (strlen($old['no_hp']) > 20) {
        $errors[] = 'Nomor HP maksimal 20 karakter.';
    } elseif (!preg_match('/^[0-9+().\-\s]+$/', $old['no_hp'])) {
        $errors[] = 'Format nomor HP tidak valid.';
    } elseif (!preg_match('/[0-9]/', $old['no_hp'])) {
        $errors[] = 'Nomor HP harus mengandung angka.';
    }
}

// Jika validasi gagal, kembalikan ke form edit
if ($errors) {
    $_SESSION['pendonor_errors'] = $errors;
    $_SESSION['pendonor_old'] = $old;

    header(
        'Location: index.php?page=pendonor-create&id=' .
        (int) $idPendonor
    );
    exit;
}

try {
    // Pastikan pendonor memang ada
    $stmt = $pdo->prepare(
        'SELECT id_pendonor FROM pendonor WHERE id_pendonor = ?'
    );
    $stmt->execute([$idPendonor]);

    if (!$stmt->fetchColumn()) {
        $_SESSION['pendonor_errors'] = ['Data pendonor tidak ditemukan.'];
        header('Location: index.php?page=pendonor-index');
        exit;
    }

    // Perbarui data identitas pendonor
    $sql = "UPDATE pendonor SET
                nama_lengkap = :nama_lengkap,
                alamat = :alamat,
                tanggal_lahir = :tanggal_lahir,
                jenis_kelamin = :jenis_kelamin,
                golongan_darah = :golongan_darah,
                no_hp = :no_hp,
                updated_at = NOW()
            WHERE id_pendonor = :id_pendonor";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nama_lengkap' => $old['nama_lengkap'],
        ':alamat' => $old['alamat'] !== '' ? $old['alamat'] : null,
        ':tanggal_lahir' => $old['tanggal_lahir'],
        ':jenis_kelamin' => $old['jenis_kelamin'],
        ':golongan_darah' => $old['golongan_darah'],
        ':no_hp' => $old['no_hp'] !== '' ? $old['no_hp'] : null,
        ':id_pendonor' => $idPendonor,
    ]);

    $_SESSION['flash_success'] = 'Data pendonor berhasil diperbarui.';
    unset($_SESSION['pendonor_errors'], $_SESSION['pendonor_old']);

    // Buat token baru setelah proses berhasil
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: index.php?page=pendonor-index');
    exit;

} catch (PDOException $e) {
    error_log('Gagal memperbarui data pendonor: ' . $e->getMessage());

    $_SESSION['pendonor_errors'] = [
        'Data belum berhasil diperbarui. Periksa log aplikasi atau hubungi administrator.'
    ];
    $_SESSION['pendonor_old'] = $old;

    header(
        'Location: index.php?page=pendonor-create&id=' .
        (int) $idPendonor
    );
    exit;
}
