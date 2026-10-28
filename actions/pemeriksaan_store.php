<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
function jsonReply($ok,$message,$code=200){http_response_code($code);echo json_encode(['success'=>$ok,'message'=>$message],JSON_UNESCAPED_UNICODE);exit;}
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonReply(false,'Metode tidak diizinkan.',405);
$st=$_SESSION['csrf_token']??'';$pt=$_POST['csrf_token']??'';
if(!$st||!$pt||!hash_equals($st,$pt)) jsonReply(false,'Token formulir tidak valid. Muat ulang halaman.',419);
$idPendonor=filter_input(INPUT_POST,'id_pendonor',FILTER_VALIDATE_INT);$idKegiatan=filter_input(INPUT_POST,'id_kegiatan',FILTER_VALIDATE_INT);$kelompok=$_POST['kelompok']??'';
if(!$idPendonor||!$idKegiatan||!in_array($kelompok,['modalVital','modalGula','modalHasil'],true)) jsonReply(false,'Pendonor, kegiatan, atau jenis pemeriksaan tidak valid.',422);
try {
 $q=$pdo->prepare('SELECT id_pendonor FROM pendonor WHERE id_pendonor=?');$q->execute([$idPendonor]);if(!$q->fetchColumn())jsonReply(false,'Pendonor tidak ditemukan.',404);
 $q=$pdo->prepare('SELECT id_kegiatan FROM kegiatan_donor WHERE id_kegiatan=?');$q->execute([$idKegiatan]);if(!$q->fetchColumn())jsonReply(false,'Kegiatan donor tidak ditemukan.',404);
 $d=['tekanan_sistolik'=>null,'tekanan_diastolik'=>null,'berat_badan'=>null,'tinggi_badan'=>null,'gula_darah'=>null,'layak_donor'=>null,'obat_diberikan'=>null,'catatan_pemeriksaan'=>null];
 if($kelompok==='modalVital'){
  foreach(['tekanan_sistolik'=>[50,300,'int'],'tekanan_diastolik'=>[30,200,'int'],'berat_badan'=>[20,300,'float'],'tinggi_badan'=>[80,250,'float']] as $key=>$rule){$raw=$_POST[$key]??'';if($raw===''||!is_numeric($raw)||$raw<$rule[0]||$raw>$rule[1]||($rule[2]==='int'&&(string)(int)$raw!==(string)$raw))jsonReply(false,'Nilai '.$key.' tidak valid.',422);$d[$key]=$rule[2]==='int'?(int)$raw:(float)$raw;}
 } elseif($kelompok==='modalGula'){$raw=$_POST['gula_darah']??'';if($raw===''||!is_numeric($raw)||$raw<12||$raw>17)jsonReply(false,'Gula darah harus bernilai 12–17 g/dL.',422);$d['gula_darah']=(float)$raw;
 } else {$raw=$_POST['layak_donor']??'';if(!in_array((string)$raw,['0','1'],true))jsonReply(false,'Pilih hasil kelayakan donor.',422);$obat=trim((string)($_POST['obat_diberikan']??''));$catatan=trim((string)($_POST['catatan_pemeriksaan']??''));if(mb_strlen($obat)>255||mb_strlen($catatan)>5000)jsonReply(false,'Obat atau catatan terlalu panjang.',422);$d['layak_donor']=(int)$raw;$d['obat_diberikan']=$obat===''?null:$obat;$d['catatan_pemeriksaan']=$catatan===''?null:$catatan;}
 $q=$pdo->prepare('SELECT id_pemeriksaan FROM pemeriksaan_donor WHERE id_pendonor=? AND id_kegiatan=? ORDER BY id_pemeriksaan DESC LIMIT 1');$q->execute([$idPendonor,$idKegiatan]);$idExisting=$q->fetchColumn();
 if($idExisting){$sql='UPDATE pemeriksaan_donor SET tekanan_sistolik=COALESCE(?,tekanan_sistolik),tekanan_diastolik=COALESCE(?,tekanan_diastolik),berat_badan=COALESCE(?,berat_badan),tinggi_badan=COALESCE(?,tinggi_badan),gula_darah=COALESCE(?,gula_darah),layak_donor=COALESCE(?,layak_donor),obat_diberikan=COALESCE(?,obat_diberikan),catatan_pemeriksaan=COALESCE(?,catatan_pemeriksaan),tanggal_pemeriksaan=NOW(),updated_at=NOW() WHERE id_pemeriksaan=?';$q=$pdo->prepare($sql);$q->execute([$d['tekanan_sistolik'],$d['tekanan_diastolik'],$d['berat_badan'],$d['tinggi_badan'],$d['gula_darah'],$d['layak_donor'],$d['obat_diberikan'],$d['catatan_pemeriksaan'],$idExisting]);
 }else{$sql='INSERT INTO pemeriksaan_donor (id_pendonor,id_kegiatan,tanggal_pemeriksaan,tekanan_sistolik,tekanan_diastolik,berat_badan,tinggi_badan,gula_darah,layak_donor,obat_diberikan,catatan_pemeriksaan,created_at,updated_at) VALUES (?,?,NOW(),?,?,?,?,?,?,?,?,NOW(),NOW())';$q=$pdo->prepare($sql);$q->execute([$idPendonor,$idKegiatan,$d['tekanan_sistolik'],$d['tekanan_diastolik'],$d['berat_badan'],$d['tinggi_badan'],$d['gula_darah'],$d['layak_donor'],$d['obat_diberikan'],$d['catatan_pemeriksaan']]);}
 jsonReply(true,'Data pemeriksaan berhasil disimpan.');
}catch(PDOException $e){error_log('Pemeriksaan donor: '.$e->getMessage());jsonReply(false,'Terjadi kesalahan database. Periksa log aplikasi.',500);}
