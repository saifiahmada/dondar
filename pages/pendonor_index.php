<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

function donorAge($date) {
    return $date ? ((int)date('Y') - (int)date('Y', strtotime($date))) : '-';
}
$pendonorList = $pdo->query("SELECT id_pendonor,nama_lengkap,jenis_kelamin,tanggal_lahir,golongan_darah FROM pendonor ORDER BY id_pendonor DESC")->fetchAll(PDO::FETCH_ASSOC);
$kegiatanList = $pdo->query("SELECT id_kegiatan,nama_kegiatan,tanggal_kegiatan FROM kegiatan_donor ORDER BY tanggal_kegiatan DESC,id_kegiatan DESC")->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Data Pendonor</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Data Pendonor</li></ol></div></div></div></div>
<section class="content"><div class="container-fluid">
<?php if (!$kegiatanList): ?><div class="alert alert-warning">Belum ada kegiatan donor. Tambahkan kegiatan pada tabel <code>kegiatan_donor</code> terlebih dahulu.</div><?php endif; ?>
<div class="card"><div class="card-header"><h3 class="card-title">Daftar Pendonor</h3><div class="card-tools"><a href="index.php?page=pendonor-create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Pendonor</a></div></div>
<div class="card-body"><div class="table-responsive">
    <table id="tabelPendonor" class="table table-bordered table-striped table-hover"><thead><tr><th>ID Pendonor</th><th>Nama Lengkap</th><th>Jenis Kelamin</th><th>Umur (tahun)</th><th>Golongan Darah</th><th>Input Pemeriksaan</th></tr></thead><tbody>
<?php foreach ($pendonorList as $d): ?><tr>
<td><?= (int)$d['id_pendonor'] ?></td><td><?= e($d['nama_lengkap']) ?></td><td><?= $d['jenis_kelamin']==='L'?'Laki-laki':'Perempuan' ?></td><td><?= e(donorAge($d['tanggal_lahir'])) ?></td><td><span class="badge badge-danger"><?= e($d['golongan_darah']) ?></span></td><td><div class="btn-group btn-group-sm">
<button type="button" class="btn btn-info btn-pemeriksaan" data-modal="modalVital" data-id="<?= (int)$d['id_pendonor'] ?>" data-nama="<?= e($d['nama_lengkap']) ?>"><i class="fas fa-heartbeat"></i> Cek Darah</button>
<button type="button" class="btn btn-warning btn-pemeriksaan" data-modal="modalGula" data-id="<?= (int)$d['id_pendonor'] ?>" data-nama="<?= e($d['nama_lengkap']) ?>"><i class="fas fa-tint"></i> Gula</button>
<button type="button" class="btn btn-success btn-pemeriksaan" data-modal="modalHasil" data-id="<?= (int)$d['id_pendonor'] ?>" data-nama="<?= e($d['nama_lengkap']) ?>"><i class="fas fa-check-circle"></i> Hasil</button>
<a href="index.php?page=pendonor-create&id=<?= (int) $d['id_pendonor'] ?>"
   class="btn btn-primary"
   title="Edit data pendonor">
    <i class="fas fa-edit"></i> Edit Data Pendonor
</a>
</div></td></tr><?php endforeach; ?>
</tbody>
</table>
</div></div></div></div>
</section>
<?php
$activityOptions = '<option value="">-- Pilih kegiatan --</option>';
foreach ($kegiatanList as $k) $activityOptions .= '<option value="'.(int)$k['id_kegiatan'].'">'.e($k['nama_kegiatan']).' — '.e($k['tanggal_kegiatan']).'</option>';
function activitySelect($options) { return '<div class="form-group"><label>Kegiatan Donor *</label><select class="form-control" name="id_kegiatan" required>'.$options.'</select></div>'; }
function modalStart($id,$title,$color,$token,$options) { echo '<div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg" role="document"><form class="form-pemeriksaan"><div class="modal-content"><div class="modal-header bg-'.$color.'"><h5 class="modal-title">'.$title.'</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body"><input type="hidden" name="csrf_token" value="'.e($token).'"><input type="hidden" name="id_pendonor"><input type="hidden" name="kelompok" value="'.$id.'"><p>Pendonor: <strong class="nama-pendonor"></strong></p>'.$options; }
function modalEnd($color) { echo '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-'.$color.'"><i class="fas fa-save"></i> Simpan pemeriksaan</button></div></div></form></div></div>'; }
modalStart('modalVital','Input Tanda Vital & Antropometri','info',$_SESSION['csrf_token'],activitySelect($activityOptions));
?>
<div class="form-row"><div class="form-group col-md-6"><label>Tekanan Sistolik (mmHg) *</label><input type="number" class="form-control" name="tekanan_sistolik" min="50" max="300" step="1" required></div><div class="form-group col-md-6"><label>Tekanan Diastolik (mmHg) *</label><input type="number" class="form-control" name="tekanan_diastolik" min="30" max="200" step="1" required></div><div class="form-group col-md-6"><label>Berat Badan (kg) *</label><input type="number" class="form-control" name="berat_badan" min="20" max="300" step="0.1" required></div><div class="form-group col-md-6"><label>Tinggi Badan (cm) *</label><input type="number" class="form-control" name="tinggi_badan" min="80" max="250" step="0.1" required></div></div><small class="text-muted">Rentang angka ini merupakan validasi input, bukan penentu kelayakan medis.</small>
<?php modalEnd('info'); modalStart('modalGula','Input Gula Darah','warning',$_SESSION['csrf_token'],activitySelect($activityOptions)); ?>
<div class="form-group"><label>Gula Darah (mg/dL) *</label><input type="number" class="form-control" name="gula_darah" min="20" max="800" step="0.1" required></div>
<?php modalEnd('warning'); modalStart('modalHasil','Hasil Pemeriksaan Donor','success',$_SESSION['csrf_token'],activitySelect($activityOptions)); ?>
<div class="form-group"><label>Layak Donor? *</label><select class="form-control" name="layak_donor" required><option value="">-- Pilih hasil --</option><option value="1">Layak donor</option><option value="0">Belum/tidak layak donor</option></select><small class="form-text text-muted">Keputusan akhir harus ditetapkan petugas/tenaga kesehatan berwenang.</small></div><div class="form-group"><label>Obat Diberikan</label><input type="text" class="form-control" name="obat_diberikan" maxlength="255"></div><div class="form-group"><label>Catatan Pemeriksaan</label><textarea class="form-control" name="catatan_pemeriksaan" rows="3" maxlength="5000"></textarea></div>
<?php modalEnd('success'); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css"><link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script><script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
$(function(){
 $('#tabelPendonor').DataTable({responsive:true,autoWidth:false,pageLength:10,order:[[0,'desc']],dom:"<'row'<'col-md-6'B><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-md-5'i><'col-md-7'p>>",buttons:[
 {extend:'copyHtml5',text:'Salin',className:'btn btn-secondary btn-sm'},
 {extend:'csvHtml5',text:'CSV',title:'Data_Pendonor',className:'btn btn-info btn-sm',exportOptions:{columns:[0,1,2,3,4]}},
 {extend:'excelHtml5',text:'Excel',title:'Data_Pendonor',className:'btn btn-success btn-sm',exportOptions:{columns:[0,1,2,3,4]}},
 {extend:'pdfHtml5',text:'PDF',title:'Data Pendonor',orientation:'landscape',pageSize:'A4',className:'btn btn-danger btn-sm',exportOptions:{columns:[0,1,2,3,4]}},
 {extend:'print',text:'Cetak',className:'btn btn-primary btn-sm',exportOptions:{columns:[0,1,2,3,4]}}],columnDefs:[{targets:5,orderable:false,searchable:false}],language:{emptyTable:'Belum ada data pendonor',info:'Menampilkan _START_ sampai _END_ dari _TOTAL_ pendonor',infoEmpty:'Tidak ada data',lengthMenu:'Tampilkan _MENU_ data',search:'Cari:',zeroRecords:'Data tidak ditemukan',paginate:{first:'Awal',last:'Akhir',next:'Berikutnya',previous:'Sebelumnya'}}});
 $('.btn-pemeriksaan').on('click',function(){var $b=$(this),$m=$('#'+$b.data('modal'));$m.find('form')[0].reset();$m.find('[name=id_pendonor]').val($b.data('id'));$m.find('.nama-pendonor').text($b.data('nama'));$m.find('[name=csrf_token]').val(<?= json_encode($_SESSION['csrf_token']) ?>);$m.modal('show');});
 $('.form-pemeriksaan').on('submit',function(ev){
    ev.preventDefault();
    var f=this;
    if(!f.checkValidity()){
        f.reportValidity();
        return;
    }
    var $f=$(f),$btn=$f.find('[type=submit]');
    $btn.prop('disabled',true);
    $.ajax({url:'index.php?action=pemeriksaan-store',type:'POST',data:$f.serialize(),dataType:'json'}).done(function(r){if(r.success){$f.closest('.modal').modal('hide');$('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>').text(r.message).append('<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>').prependTo('.content .container-fluid').delay(5000).fadeOut(300,function(){$(this).remove();});}else{alert(r.message||'Gagal menyimpan data.');}}).fail(function(x){alert(x.responseJSON&&x.responseJSON.message?x.responseJSON.message:'Terjadi kesalahan saat menyimpan pemeriksaan.');}).always(function(){$btn.prop('disabled',false);});});
});
</script>
