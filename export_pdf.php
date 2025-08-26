<?php
require 'config.php';
if (!is_admin()) { header('Location: dashboard.php'); exit; }
require 'vendor/autoload.php';
use Dompdf\Dompdf;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$stmt = $pdo->prepare("SELECT * FROM laporan_perbaikan WHERE MONTH(tanggal_input)=? AND YEAR(tanggal_input)=? ORDER BY tanggal_input ASC");
$stmt->execute([$bulan, $tahun]);
$data = $stmt->fetchAll();

function weekOfMonth($date) {
    $dt = new DateTime($date);
    $first = new DateTime($dt->format('Y-m-01'));
    $diff = (int)$dt->format('j') - 1;
    return intdiv($diff, 7) + 1;
}
$weeks = [];
foreach ($data as $row) {
    $w = weekOfMonth($row['tanggal_input']);
    $weeks[$w][] = $row;
}

ob_start();
?>
<!doctype html>
<html><head><meta charset="utf-8"><style>
body{font-family: Arial, sans-serif; font-size:12px;}
.header{ text-align:center; }
.table{ width:100%; border-collapse: collapse; margin-top:10px;}
.table th, .table td{ border:1px solid #333; padding:6px; }
</style></head><body>
<?php foreach($weeks as $w => $rows): ?>
  <div class="header">
    <img src="<?php echo realpath('assets/images/logo_kce_transparent.png'); ?>" style="height:50px"><br>
    <strong>PT. Kalimantan Concrete Engineering</strong><br>
    <strong>LAPORAN PERBAIKAN MEKANIK</strong><br>
    <span>PERIODE: <?=strtoupper($bulan).' '.$tahun?> - M<?=$w?></span>
  </div>
  <table class="table">
    <thead><tr>
      <th>Hari/Tanggal</th><th>Nama Unit</th><th>Keluhan/Kerusakan</th><th>Penyebab</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Tindakan</th>
    </tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=date('d/m/Y', strtotime($r['tanggal_input']))?></td>
          <td><?=htmlspecialchars($r['nama_unit'])?></td>
          <td><?=nl2br(htmlspecialchars($r['keluhan_kerusakan']))?></td>
          <td><?=nl2br(htmlspecialchars($r['penyebab_kerusakan']))?></td>
          <td><?=!empty($r['tgl_mulai_reparasi'])?date('d/m/Y', strtotime($r['tgl_mulai_reparasi'])):''?></td>
          <td><?=!empty($r['tgl_selesai_reparasi'])?date('d/m/Y', strtotime($r['tgl_selesai_reparasi'])):''?></td>
          <td><?=nl2br(htmlspecialchars($r['tindakan_perbaikan']))?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div style="page-break-after:always"></div>
<?php endforeach; ?>
</body></html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();
$dompdf->stream('Laporan_'.$bulan.'_'.$tahun.'.pdf', ['Attachment' => 1]);
exit;
