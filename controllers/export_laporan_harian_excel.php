<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$bulan  = (int)($_GET['bulan'] ?? date('n'));
$tahun  = (int)($_GET['tahun'] ?? date('Y'));
$minggu = $_GET['minggu'] ?? 'Semua';

$sql = "
SELECT
  lp.tanggal_input        AS tgl,
  lp.nama_unit            AS unit,
  lp.keluhan_kerusakan    AS keluhan,
  lp.penyebab_kerusakan   AS penyebab,
  lp.tgl_mulai_reparasi   AS tgl_mulai,
  lp.tgl_selesai_reparasi AS tgl_selesai,
  lp.tindakan_perbaikan   AS tindakan,
  COALESCE(u.role,u.nama,u.username, CAST(lp.created_by AS CHAR)) AS diinput_label
FROM laporan_perbaikan lp
LEFT JOIN users u ON lp.created_by = u.id
WHERE MONTH(lp.tanggal_input)=? AND YEAR(lp.tanggal_input)=?
";
$params = [$bulan,$tahun];
if ($minggu!=='Semua'){
  $m=max(1,min(5,(int)$minggu));
  $sql.=" AND CASE
            WHEN DAY(lp.tanggal_input) BETWEEN 1 AND 7 THEN 1
            WHEN DAY(lp.tanggal_input) BETWEEN 8 AND 14 THEN 2
            WHEN DAY(lp.tanggal_input) BETWEEN 15 AND 21 THEN 3
            WHEN DAY(lp.tanggal_input) BETWEEN 22 AND 28 THEN 4
            ELSE 5 END = ? ";
  $params[]=$m;
}
$sql.=" ORDER BY lp.tanggal_input DESC, lp.id DESC";
$st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll(PDO::FETCH_ASSOC);

$ss=new Spreadsheet(); $ws=$ss->getActiveSheet();
$ws->fromArray(["HARI/TANGGAL","NAMA UNIT","KELUHAN/KERUSAKAN","PENYEBAB","TGL MULAI","TGL SELESAI","TINDAKAN PERBAIKAN","DIINPUT OLEH"], null, 'A1');
$r=2;
foreach($rows as $row){
  $ws->fromArray([
    $row['tgl']?date('d/m/Y',strtotime($row['tgl'])):'',
    $row['unit']??'',
    $row['keluhan']??'',
    $row['penyebab']??'',
    $row['tgl_mulai']?date('d/m/Y',strtotime($row['tgl_mulai'])):'',
    $row['tgl_selesai']?date('d/m/Y',strtotime($row['tgl_selesai'])):'',
    $row['tindakan']??'',
    $row['diinput_label']??'',
  ], null, 'A'.$r);
  $r++;
}
$fn = "Laporan_Harian_Mekanik_{$tahun}-".str_pad($bulan,2,'0',STR_PAD_LEFT).($minggu==='Semua'?'':"_M{$minggu}").".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$fn\"");
$writer=new Xlsx($ss); $writer->save('php://output'); exit;
