<?php

// pages/pendonor_create.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Deteksi mode tambah atau edit
$idPendonor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEdit = $idPendonor !== false && $idPendonor !== null && $idPendonor > 0;

$pendonor = [
    'nama_lengkap' => '',
    'alamat' => '',
    'tanggal_lahir' => '',
    'jenis_kelamin' => '',
    'golongan_darah' => '',
    'no_hp' => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare(
        'SELECT id_pendonor, nama_lengkap, alamat, tanggal_lahir,
                jenis_kelamin, golongan_darah, no_hp
         FROM pendonor
         WHERE id_pendonor = ?'
    );
    $stmt->execute([$idPendonor]);
    $dataPendonor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dataPendonor) {
        http_response_code(404);
        echo '<div class="alert alert-danger">Data pendonor tidak ditemukan.</div>';
        return;
    }

    $pendonor = $dataPendonor;
}

// Data lama dari sesi, jika validasi penyimpanan sebelumnya gagal
$errors = $_SESSION['pendonor_errors'] ?? [];
$old = array_merge(
    $pendonor,
    $_SESSION['pendonor_old'] ?? []
);

$flashSuccess = $_SESSION['flash_success'] ?? '';

unset(
    $_SESSION['pendonor_errors'],
    $_SESSION['pendonor_old'],
    $_SESSION['flash_success']
);

$today = date('Y-m-d');
$golonganDarah = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

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
    .help-text { font-size: .85rem; color: #6c757d; }
</style>

<?php if ($flashSuccess !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i><?= e($flashSuccess) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
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
                    <i class="fas fa-id-card mr-2"></i><?= $isEdit ? 'Edit Data Pendonor' : 'Formulir Identitas Pendonor' ?>
                </h3>
            </div>

            <form id="formPendonor"
      method="post"
      action="<?= $isEdit
          ? 'index.php?action=pendonor-update'
          : 'index.php?page=pendonor-create' ?>"
      autocomplete="on"
      novalidate>

    <input type="hidden"
           name="csrf_token"
           value="<?= e($_SESSION['csrf_token']) ?>">

    <?php if ($isEdit): ?>
        <input type="hidden"
               name="id_pendonor"
               value="<?= (int) $pendonor['id_pendonor'] ?>">
    <?php endif; ?>

                <div class="card-body">
                    <p class="text-muted">
                        Lengkapi identitas pendonor. Kolom bertanda
                        <span class="required-mark">*</span> wajib diisi.
                    </p>

                    <h5 class="form-section-title">
                        <i class="fas fa-user text-primary mr-2"></i>Identitas Pendonor
                    </h5>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="required-mark">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               maxlength="100"
                               value="<?= e($old['nama_lengkap'] ?? '') ?>"
                               placeholder="Masukkan nama lengkap"
                               autocomplete="name"
                               required>
                        <div class="invalid-feedback">Nama lengkap wajib diisi (maksimal 100 karakter).</div>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir <span class="required-mark">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="tanggal_lahir"
                               name="tanggal_lahir"
                               max="<?= e($today) ?>"
                               value="<?= e($old['tanggal_lahir'] ?? '') ?>"
                               required>
                        <div class="invalid-feedback">Pilih tanggal lahir yang valid dan tidak melebihi hari ini.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="jenis_kelamin">Jenis Kelamin <span class="required-mark">*</span></label>
                            <select class="custom-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">-- Pilih jenis kelamin --</option>
                                <option value="L" <?= ($old['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= ($old['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                            <div class="invalid-feedback">Silakan pilih jenis kelamin.</div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="golongan_darah">Golongan Darah <span class="required-mark">*</span></label>
                            <select class="custom-select" id="golongan_darah" name="golongan_darah" required>
                                <option value="">-- Pilih golongan darah --</option>
                                <?php foreach ($golonganDarah as $golongan): ?>
                                    <option value="<?= e($golongan) ?>"
                                        <?= ($old['golongan_darah'] ?? '') === $golongan ? 'selected' : '' ?>>
                                        <?= e($golongan) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Silakan pilih golongan darah.</div>
                        </div>
                    </div>

                    <h5 class="form-section-title mt-4">
                        <i class="fas fa-address-book text-primary mr-2"></i>Informasi Kontak
                    </h5>

                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea class="form-control"
                                  id="alamat"
                                  name="alamat"
                                  rows="3"
                                  maxlength="5000"
                                  placeholder="Nama jalan, kelurahan/desa, kecamatan, dan kota"
                                  autocomplete="street-address"><?= e($old['alamat'] ?? '') ?></textarea>
                        <div class="invalid-feedback">Alamat maksimal 5.000 karakter.</div>
                    </div>

                    <div class="form-group">
                        <label for="no_hp">Nomor HP</label>
                        <input type="tel"
                               class="form-control"
                               id="no_hp"
                               name="no_hp"
                               maxlength="20"
                               inputmode="tel"
                               value="<?= e($old['no_hp'] ?? '') ?>"
                               placeholder="Contoh: 081234567890"
                               autocomplete="tel"
                               pattern="[0-9+().\-\s]{1,20}"
                               aria-describedby="no_hp_help">
                        <div class="invalid-feedback">Nomor HP maksimal 20 karakter; gunakan angka, spasi, +, tanda kurung, titik, atau tanda hubung.</div>
                        <small id="no_hp_help" class="help-text">
                            Nomor HP tidak wajib diisi. Jika diisi, pastikan nomor benar dan aktif.
                        </small>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between flex-wrap">
                    <button type="reset" class="btn btn-default mb-2 mb-sm-0">
    <i class="fas fa-undo mr-1"></i>
    <?= $isEdit ? 'Reset Perubahan' : 'Reset Form' ?>
</button>
                    <button type="submit" class="btn btn-primary">
    <i class="fas fa-save mr-1"></i>
    <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pendonor' ?>
</button>
                </div>
            </form>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            Hasil pemeriksaan dan kelayakan donor dicatat pada formulir pemeriksaan terpisah.
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
                    <li class="mb-2">Periksa kembali tanggal lahir.</li>
                    <li class="mb-2">Pilih golongan darah sesuai informasi yang telah diverifikasi.</li>
                    <li class="mb-2">Masukkan nomor HP aktif jika tersedia.</li>
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

<script>
(function () {
    'use strict';

    const form = document.getElementById('formPendonor');
    const nama = document.getElementById('nama_lengkap');
    const tanggalLahir = document.getElementById('tanggal_lahir');
    const alamat = document.getElementById('alamat');
    const noHp = document.getElementById('no_hp');

    function validateField(field) {
        if (field === nama) {
            field.setCustomValidity(
                field.value.trim() === '' ? 'Nama lengkap wajib diisi.' :
                field.value.trim().length > 100 ? 'Nama maksimal 100 karakter.' : ''
            );
        } else if (field === tanggalLahir) {
            field.setCustomValidity(
                field.value === '' ? 'Tanggal lahir wajib diisi.' :
                field.value > field.max ? 'Tanggal lahir tidak boleh di masa depan.' : ''
            );
        } else if (field === alamat) {
            field.setCustomValidity(
                field.value.length > 5000 ? 'Alamat maksimal 5.000 karakter.' : ''
            );
        } else if (field === noHp) {
            const value = field.value.trim();
            const validChars = /^[0-9+().\-\s]*$/.test(value);
            field.setCustomValidity(
                value.length > 20 ? 'Nomor HP maksimal 20 karakter.' :
                !validChars ? 'Format nomor HP tidak valid.' :
                (value !== '' && !/[0-9]/.test(value)) ? 'Nomor HP harus mengandung angka.' : ''
            );
        }

        field.classList.toggle('is-invalid', !field.checkValidity());
        field.classList.toggle('is-valid', field.checkValidity() && field.value !== '');
    }

    [nama, tanggalLahir, alamat, noHp,
     document.getElementById('jenis_kelamin'),
     document.getElementById('golongan_darah')].forEach(function (field) {
        field.addEventListener('input', function () { validateField(field); });
        field.addEventListener('change', function () { validateField(field); });
    });

    form.addEventListener('submit', function (event) {
        [nama, tanggalLahir, alamat, noHp,
         document.getElementById('jenis_kelamin'),
         document.getElementById('golongan_darah')].forEach(validateField);

        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();

            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) firstInvalid.focus();
        }

        form.classList.add('was-validated');
    });

    form.addEventListener('reset', function () {
        window.setTimeout(function () {
            form.querySelectorAll('.is-valid, .is-invalid').forEach(function (field) {
                field.classList.remove('is-valid', 'is-invalid');
                field.setCustomValidity('');
            });
        }, 0);
    });
})();
</script>
