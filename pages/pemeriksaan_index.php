
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
|--------------------------------------------------------------------------
| Filter kegiatan donor
|--------------------------------------------------------------------------
*/
$idKegiatan = filter_input(INPUT_GET, 'id_kegiatan', FILTER_VALIDATE_INT);

if (!$idKegiatan || $idKegiatan < 1) {
    $idKegiatan = null;
}

/*
|--------------------------------------------------------------------------
| Daftar kegiatan untuk filter
|--------------------------------------------------------------------------
*/
$kegiatanList = $pdo->query("
    SELECT id_kegiatan, nama_kegiatan, tanggal_kegiatan
    FROM kegiatan_donor
    ORDER BY tanggal_kegiatan DESC, id_kegiatan DESC
")->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Query data pemeriksaan donor
|--------------------------------------------------------------------------
| Relasi yang benar:
| pemeriksaan_donor.id_pendonor = pendonor.id_pendonor
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        p.id_pemeriksaan,
        p.id_pendonor,
        p.id_kegiatan,
        d.nama_lengkap,
        d.jenis_kelamin,
        d.golongan_darah,
        k.nama_kegiatan,
        p.tanggal_pemeriksaan,
        p.tekanan_sistolik,
        p.tekanan_diastolik,
        p.berat_badan,
        p.tinggi_badan,
        p.gula_darah,
        p.layak_donor,
        p.obat_diberikan,
        p.catatan_pemeriksaan
    FROM pemeriksaan_donor AS p
    LEFT JOIN pendonor AS d
        ON d.id_pendonor = p.id_pendonor
    LEFT JOIN kegiatan_donor AS k
        ON k.id_kegiatan = p.id_kegiatan
";

$params = [];

if ($idKegiatan !== null) {
    $sql .= " WHERE p.id_kegiatan = ?";
    $params[] = $idKegiatan;
}

$sql .= "
    ORDER BY p.tanggal_pemeriksaan DESC, p.id_pemeriksaan DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pemeriksaanList = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Helper tampilan
|--------------------------------------------------------------------------
*/
function tampilNilai($value, $suffix = '') {
    if ($value === null || $value === '') {
        return '-';
    }

    return e((string) $value . $suffix);
}

function tampilTanggalPemeriksaan($value) {
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);

    return $timestamp
        ? date('d-m-Y H:i', $timestamp)
        : '-';
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Data Pemeriksaan Donor</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="index.php">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Data Pemeriksaan
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if (!$kegiatanList): ?>
            <div class="alert alert-warning">
                Belum ada kegiatan donor. Tambahkan kegiatan terlebih
                dahulu sebelum melakukan pemeriksaan.
            </div>
        <?php endif; ?>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-heartbeat mr-1"></i>
                    Daftar Pemeriksaan Donor
                </h3>
            </div>

            <div class="card-body">

                <form method="GET" action="index.php"
                      class="form-inline mb-3">
                    <input type="hidden" name="page"
                           value="pemeriksaan-index">

                    <label for="id_kegiatan" class="mr-2">
                        Kegiatan Donor
                    </label>

                    <select name="id_kegiatan" id="id_kegiatan"
                            class="form-control mr-2 mb-2">
                        <option value="">Semua Kegiatan</option>

                        <?php foreach ($kegiatanList as $k): ?>
                            <option
                                value="<?= (int) $k['id_kegiatan'] ?>"
                                <?= $idKegiatan === (int) $k['id_kegiatan']
                                    ? 'selected'
                                    : '' ?>>
                                <?= e($k['nama_kegiatan']) ?>
                                (<?= e($k['tanggal_kegiatan']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit"
                            class="btn btn-primary mr-2 mb-2">
                        <i class="fas fa-filter"></i> Tampilkan
                    </button>

                    <a href="index.php?page=pemeriksaan-index"
                       class="btn btn-secondary mb-2">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </form>

                <div class="table-responsive">
                    <table id="tabelPemeriksaan"
                           class="table table-bordered table-striped
                                  table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Pendonor</th>
                                <th>Golongan Darah</th>
                                <th>Kegiatan Donor</th>
                                <th>Tanggal Pemeriksaan</th>
                                <th>Tekanan Darah</th>
                                <th>Berat (kg)</th>
                                <th>Tinggi (cm)</th>
                                <th>Gula Darah (mg/dL)</th>
                                <th>Hasil Kelayakan</th>
                                <th>Obat Diberikan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pemeriksaanList as $p): ?>
                                <tr>
                                    <td>
                                        <?= (int) $p['id_pemeriksaan'] ?>
                                    </td>

                                    <td>
                                        <?= e($p['nama_lengkap'] ?? 'Pendonor tidak ditemukan') ?>
                                    </td>

                                    <td>
                                        <?php if ($p['golongan_darah']): ?>
                                            <span class="badge badge-danger">
                                                <?= e($p['golongan_darah']) ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= e($p['nama_kegiatan'] ?? 'Kegiatan tidak ditemukan') ?>
                                    </td>

                                    <td data-order="<?= e($p['tanggal_pemeriksaan'] ?? '') ?>">
                                        <?= e(tampilTanggalPemeriksaan($p['tanggal_pemeriksaan'])) ?>
                                    </td>

                                    <td>
                                        <?php if (
                                            $p['tekanan_sistolik'] !== null
                                            && $p['tekanan_diastolik'] !== null
                                        ): ?>
                                            <?= (int) $p['tekanan_sistolik'] ?>
                                            /
                                            <?= (int) $p['tekanan_diastolik'] ?>
                                            mmHg
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= tampilNilai($p['berat_badan']) ?>
                                    </td>

                                    <td>
                                        <?= tampilNilai($p['tinggi_badan']) ?>
                                    </td>

                                    <td>
                                        <?= tampilNilai($p['gula_darah']) ?>
                                    </td>

                                    <td>
                                        <?php if ($p['layak_donor'] === null): ?>
                                            <span class="badge badge-secondary">
                                                Belum ditentukan
                                            </span>
                                        <?php elseif ((int) $p['layak_donor'] === 1): ?>
                                            <span class="badge badge-success">
                                                Layak
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                Tidak Layak
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= tampilNilai($p['obat_diberikan']) ?>
                                    </td>

                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm btn-edit-pemeriksaan"
                                            data-record="<?= e(json_encode(
                                                $p,
                                                JSON_HEX_APOS | JSON_HEX_QUOT |
                                                JSON_HEX_TAG | JSON_HEX_AMP
                                            )) ?>"
                                            title="Edit pemeriksaan">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


<div class="modal fade" id="modalEditPemeriksaan" tabindex="-1"
     role="dialog" aria-labelledby="judulEditPemeriksaan"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <form id="formEditPemeriksaan">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="judulEditPemeriksaan">
                        <i class="fas fa-edit"></i> Edit Data Pemeriksaan
                    </h5>
                    <button type="button" class="close"
                            data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_pemeriksaan">

                    <div class="alert alert-light border">
                        <strong>Pendonor:</strong>
                        <span id="editNamaPendonor">-</span><br>
                        <strong>Kegiatan:</strong>
                        <span id="editNamaKegiatan">-</span><br>
                        <strong>Tanggal:</strong>
                        <span id="editTanggalPemeriksaan">-</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tekanan Sistolik (mmHg)</label>
                                <input type="number" class="form-control"
                                       name="tekanan_sistolik"
                                       min="50" max="300" step="1">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tekanan Diastolik (mmHg)</label>
                                <input type="number" class="form-control"
                                       name="tekanan_diastolik"
                                       min="30" max="200" step="1">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Berat Badan (kg)</label>
                                <input type="number" class="form-control"
                                       name="berat_badan"
                                       min="20" max="300" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tinggi Badan (cm)</label>
                                <input type="number" class="form-control"
                                       name="tinggi_badan"
                                       min="80" max="250" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gula Darah (mg/dL)</label>
                                <input type="number" class="form-control"
                                       name="gula_darah"
                                       min="20" max="800" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hasil Kelayakan Donor</label>
                                <select class="form-control" name="layak_donor">
                                    <option value="">Belum ditentukan</option>
                                    <option value="1">Layak</option>
                                    <option value="0">Tidak Layak</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Obat yang Diberikan</label>
                                <input type="text" class="form-control"
                                       name="obat_diberikan" maxlength="255">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Catatan Pemeriksaan</label>
                                <textarea class="form-control"
                                          name="catatan_pemeriksaan"
                                          rows="4" maxlength="5000"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>



                </div>

                <?php if (!$pemeriksaanList): ?>
                    <div class="text-muted mt-2">
                        Belum ada data pemeriksaan untuk filter yang dipilih.
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</section>

<!-- DataTables dan tombol ekspor -->
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet"
      href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(function () {
    $('#tabelPemeriksaan').DataTable({
        responsive: false,
        scrollX: true,
        autoWidth: false,
        pageLength: 10,
        order: [[4, 'desc']],
        dom: "<'row'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Data Pemeriksaan Donor',
                // exportOptions: { columns: ':visible' }
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Data Pemeriksaan Donor',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Data Pemeriksaan Donor',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Cetak',
                className: 'btn btn-secondary btn-sm',
                title: 'Data Pemeriksaan Donor',
                exportOptions: { columns: ':visible' }
            }
        ],
        language: {
            emptyTable: 'Belum ada data pemeriksaan',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data untuk ditampilkan',
            infoFiltered: '(difilter dari _MAX_ total data)',
            lengthMenu: 'Tampilkan _MENU_ data',
            loadingRecords: 'Memuat data...',
            processing: 'Memproses...',
            search: 'Cari:',
            zeroRecords: 'Data yang dicari tidak ditemukan',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            },
            buttons: {
                print: 'Cetak'
            }
        }
    });
});


$(function () {
    var $modal = $('#modalEditPemeriksaan');
    var form = document.getElementById('formEditPemeriksaan');

    // Membuka modal dan mengisi data pemeriksaan.
    $('#tabelPemeriksaan').on('click', '.btn-edit-pemeriksaan', function () {
        var data;

        try {
            data = JSON.parse(this.getAttribute('data-record'));
        } catch (error) {
            alert('Data pemeriksaan gagal dibaca.');
            return;
        }

        form.reset();

        form.elements['id_pemeriksaan'].value = data.id_pemeriksaan || '';
        form.elements['csrf_token'].value =
            <?= json_encode($_SESSION['csrf_token']) ?>;

        form.elements['tekanan_sistolik'].value =
            data.tekanan_sistolik ?? '';
        form.elements['tekanan_diastolik'].value =
            data.tekanan_diastolik ?? '';
        form.elements['berat_badan'].value =
            data.berat_badan ?? '';
        form.elements['tinggi_badan'].value =
            data.tinggi_badan ?? '';
        form.elements['gula_darah'].value =
            data.gula_darah ?? '';
        form.elements['layak_donor'].value =
            data.layak_donor === null ? '' : String(data.layak_donor);
        form.elements['obat_diberikan'].value =
            data.obat_diberikan ?? '';
        form.elements['catatan_pemeriksaan'].value =
            data.catatan_pemeriksaan ?? '';

        $('#editNamaPendonor').text(data.nama_lengkap || '-');
        $('#editNamaKegiatan').text(data.nama_kegiatan || '-');
        $('#editTanggalPemeriksaan').text(
            data.tanggal_pemeriksaan || '-'
        );

        $modal.modal('show');
    });

    // Menyimpan perubahan melalui AJAX.
    $('#formEditPemeriksaan').on('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        var $button = $(form).find('button[type="submit"]');
        var originalHtml = $button.html();

        $button.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'index.php?action=pemeriksaan-update',
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json'
        })
        .done(function (response) {
            if (response.success) {
                $modal.modal('hide');
                alert(response.message || 'Pemeriksaan berhasil diperbarui.');
                window.location.reload();
            } else {
                alert(response.message || 'Gagal memperbarui pemeriksaan.');
            }
        })
        .fail(function (xhr) {
            var message = 'Terjadi kesalahan saat memperbarui pemeriksaan.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            alert(message);
        })
        .always(function () {
            $button.prop('disabled', false).html(originalHtml);
        });
    });
});


</script>
