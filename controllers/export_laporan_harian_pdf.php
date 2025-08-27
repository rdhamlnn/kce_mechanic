<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;

$bulan  = (int)($_GET['bulan'] ?? date('n'));
$tahun  = (int)($_GET['tahun'] ?? date('Y'));
$minggu = $_GET['minggu'] ?? 'Semua';
$bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'][$bulan];

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

function tgl($s){ return $s?date('d/m/Y',strtotime($s)):''; }

ob_start(); ?>
<html><head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
h2 { margin:0 0 8px 0; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #aaa; padding:6px; vertical-align: top; }
th { background:#e5efff; }
</style>
</head><body>
<h2>Laporan Harian Mekanik - <?=$bulanNama?> <?=$tahun?> <?=$minggu!=='Semua'?'(Minggu '.$minggu.')':''?></h2>
<table>
<tr>
  <th>HARI/TANGGAL</th><th>NAMA UNIT</th><th>KELUHAN/KERUSAKAN</th><th>PENYEBAB</th>
  <th>TGL MULAI</th><th>TGL SELESAI</th><th>TINDAKAN PERBAIKAN</th><th>DIINPUT OLEH</th>
</tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?=tgl($r['tgl'])?></td>
  <td><?=htmlspecialchars($r['unit'])?></td>
  <td><?=nl2br(htmlspecialchars($r['keluhan']))?></td>
  <td><?=nl2br(htmlspecialchars($r['penyebab']))?></td>
  <td><?=tgl($r['tgl_mulai'])?></td>
  <td><?=tgl($r['tgl_selesai'])?></td>
  <td><?=nl2br(htmlspecialchars($r['tindakan']))?></td>
  <td><?=htmlspecialchars($r['diinput_label'])?></td>
</tr>
<?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="8" align="center">Tidak ada data.</td></tr><?php endif; ?>
</table>
</body></html>
<?php
$html = ob_get_clean();
$pdf = new Dompdf(); $pdf->loadHtml($html); $pdf->setPaper('A4','landscape'); $pdf->render();
$fn = "Laporan_Harian_Mekanik_{$tahun}-".str_pad($bulan,2,'0',STR_PAD_LEFT).($minggu==='Semua'?'':"_M{$minggu}").".pdf";
$pdf->stream($fn, ["Attachment"=>true]); exit;
