
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function updateReply($success, $message, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    updateReply(false, 'Metode tidak diizinkan.', 405);
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$postToken = $_POST['csrf_token'] ?? '';

if (
    !$sessionToken ||
    !$postToken ||
    !hash_equals($sessionToken, $postToken)
) {
    updateReply(false, 'Token formulir tidak valid. Muat ulang halaman.', 419);
}

$id = filter_var(
    $_POST['id_pemeriksaan'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    updateReply(false, 'ID pemeriksaan tidak valid.', 422);
}

/*
|--------------------------------------------------------------------------
| Validasi nilai numerik opsional
|--------------------------------------------------------------------------
*/
function optionalNumber($key, $min, $max, $integer = false)
{
    $raw = trim((string) ($_POST[$key] ?? ''));

    if ($raw === '') {
        return null;
    }

    if (!is_numeric($raw)) {
        updateReply(false, 'Nilai ' . $key . ' harus berupa angka.', 422);
    }

    $value = (float) $raw;

    if ($value < $min || $value > $max) {
        updateReply(
            false,
            'Nilai ' . $key . ' harus antara ' . $min . ' dan ' . $max . '.',
            422
        );
    }

    if ($integer && floor($value) != $value) {
        updateReply(false, 'Nilai ' . $key . ' harus bilangan bulat.', 422);
    }

    if (!$integer && strpos($raw, '.') !== false) {
        $decimalPart = substr(strrchr($raw, '.'), 1);

        if (strlen($decimalPart) > 2) {
            updateReply(false, 'Nilai ' . $key . ' maksimal 2 angka desimal.', 422);
        }
    }

    return $integer ? (int) $value : $value;
}

$tekananSistolik = optionalNumber('tekanan_sistolik', 50, 300, true);
$tekananDiastolik = optionalNumber('tekanan_diastolik', 30, 200, true);
$beratBadan = optionalNumber('berat_badan', 20, 300);
$tinggiBadan = optionalNumber('tinggi_badan', 80, 250);
// ini sebenarnya untuk HB bukan gula dara
$gulaDarah = optionalNumber('gula_darah', 12, 17);

/*
|--------------------------------------------------------------------------
| Validasi hasil kelayakan donor
|--------------------------------------------------------------------------
*/
$rawLayak = trim((string) ($_POST['layak_donor'] ?? ''));

if (!in_array($rawLayak, ['', '0', '1'], true)) {
    updateReply(false, 'Hasil kelayakan donor tidak valid.', 422);
}

$layakDonor = $rawLayak === '' ? null : (int) $rawLayak;

$obat = trim((string) ($_POST['obat_diberikan'] ?? ''));
$catatan = trim((string) ($_POST['catatan_pemeriksaan'] ?? ''));

if (mb_strlen($obat, 'UTF-8') > 255) {
    updateReply(false, 'Obat yang diberikan maksimal 255 karakter.', 422);
}

if (mb_strlen($catatan, 'UTF-8') > 5000) {
    updateReply(false, 'Catatan pemeriksaan maksimal 5000 karakter.', 422);
}

$obat = $obat === '' ? null : $obat;
$catatan = $catatan === '' ? null : $catatan;

try {
    $check = $pdo->prepare(
        'SELECT id_pemeriksaan
         FROM pemeriksaan_donor
         WHERE id_pemeriksaan = ?'
    );
    $check->execute([$id]);

    if (!$check->fetchColumn()) {
        updateReply(false, 'Data pemeriksaan tidak ditemukan.', 404);
    }

    $sql = "
        UPDATE pemeriksaan_donor
        SET
            tekanan_sistolik = ?,
            tekanan_diastolik = ?,
            berat_badan = ?,
            tinggi_badan = ?,
            gula_darah = ?,
            layak_donor = ?,
            obat_diberikan = ?,
            catatan_pemeriksaan = ?,
            updated_at = NOW()
        WHERE id_pemeriksaan = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $tekananSistolik,
        $tekananDiastolik,
        $beratBadan,
        $tinggiBadan,
        $gulaDarah,
        $layakDonor,
        $obat,
        $catatan,
        $id
    ]);

    updateReply(true, 'Data pemeriksaan berhasil diperbarui.');

} catch (PDOException $e) {
    error_log('Pemeriksaan donor update: ' . $e->getMessage());
    updateReply(false, 'Terjadi kesalahan database saat memperbarui pemeriksaan.', 500);
}
