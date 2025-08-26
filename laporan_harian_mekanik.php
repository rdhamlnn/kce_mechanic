<?php
require 'config.php';
if (!is_logged_in()) { header('Location: index.php'); exit; }
// ini_set('display_errors',1); error_reporting(E_ALL);

// === Filter Bulan/Tahun/Minggu ===
$bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$bulan  = isset($_GET['bulan'])  ? max(1,min(12,(int)$_GET['bulan'])) : (int)date('n');
$tahun  = isset($_GET['tahun'])  ? (int)$_GET['tahun'] : (int)date('Y');
$minggu = $_GET['minggu'] ?? 'Semua'; // "Semua" atau 1..5

// === Query: pakai kolom yang kamu punya persis ===
$sql = "
SELECT
  lp.id,
  lp.tanggal_input              AS tgl,
  lp.nama_unit                  AS unit,
  lp.keluhan_kerusakan          AS keluhan,
  lp.penyebab_kerusakan         AS penyebab,
  lp.tgl_mulai_reparasi         AS tgl_mulai,
  lp.tgl_selesai_reparasi       AS tgl_selesai,
  lp.tindakan_perbaikan         AS tindakan,
  COALESCE(u.role, u.nama, u.username, CAST(lp.created_by AS CHAR)) AS diinput_label
FROM laporan_perbaikan lp
LEFT JOIN users u ON lp.created_by = u.id
WHERE MONTH(lp.tanggal_input) = ? AND YEAR(lp.tanggal_input) = ?
";
$params = [$bulan, $tahun];

// Minggu ke-1..5 di dalam bulan (1-7 -> 1, 8-14 -> 2, 15-21 -> 3, 22-28 -> 4, >=29 -> 5)
if ($minggu !== 'Semua') {
  $m = max(1, min(5, (int)$minggu));
  $sql .= " AND CASE
              WHEN DAY(lp.tanggal_input) BETWEEN 1 AND 7 THEN 1
              WHEN DAY(lp.tanggal_input) BETWEEN 8 AND 14 THEN 2
              WHEN DAY(lp.tanggal_input) BETWEEN 15 AND 21 THEN 3
              WHEN DAY(lp.tanggal_input) BETWEEN 22 AND 28 THEN 4
              ELSE 5
            END = ? ";
  $params[] = $m;
}
$sql .= " ORDER BY lp.tanggal_input DESC, lp.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function tgl_id($s){ return $s ? date('d/m/Y', strtotime($s)) : ''; }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laporan Harian Mekanik</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .toolbar { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .btn { display:inline-block; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; }
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-outline { background:#fff; color:#1f2937; border:1px solid #cbd5e1; }
    .btn-warning { background:#f59e0b; color:#111827; }
    .btn-danger  { background:#ef4444; color:#fff; }
    .table-wrap table { border-collapse:separate; border-spacing:0; width:100%; }
    .table-wrap th { background:#1d4ed8; color:#fff; padding:10px; font-weight:700; }
    .table-wrap tr:first-child th:first-child { border-top-left-radius:10px; }
    .table-wrap tr:first-child th:last-child  { border-top-right-radius:10px; }
    .table-wrap td { background:#fff; padding:10px; border-bottom:1px solid #e5e7eb; vertical-align:top; }
    .filter-box { display:flex; gap:8px; flex-wrap:wrap; }
    .filter-box select, .filter-box input[type="number"] { padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px; }
  </style>
</head>
<body>
<div class="layout">
  <?php include 'partials/sidebar.php'; ?>
  <div>
    <header class="header">
      <img src="assets/images/logo_kce_transparent.png" alt="KCE" class="logo-header">
      <div class="user-info">
        Halo, <?=htmlspecialchars($_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'user')?> |
        <a href="logout.php">Logout</a>
      </div>
    </header>

    <main class="main">
      <div class="card" style="padding:18px;">
        <h1 style="margin:0 0 4px;">Laporan Harian Mekanik</h1>
        <div style="height:3px; width:60px; background:#22c55e; border-radius:3px; margin:8px 0 16px;"></div>

        <!-- Filter -->
        <form method="get" class="card" style="padding:10px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:12px;">
          <div class="filter-box">
            <div>
              <label>Bulan:</label><br>
              <select name="bulan">
                <?php foreach ($bulanNama as $num=>$name): ?>
                  <option value="<?=$num?>" <?=$num==$bulan?'selected':''?>><?=$name?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label>Tahun:</label><br>
              <input type="number" name="tahun" value="<?=$tahun?>" min="2000" max="2100" />
            </div>
            <div>
              <label>Minggu:</label><br>
              <select name="minggu">
                <option <?=$minggu==='Semua'?'selected':''?>>Semua</option>
                <?php for ($i=1;$i<=5;$i++): ?>
                  <option value="<?=$i?>" <?=$minggu==(string)$i?'selected':''?>><?=$i?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div style="align-self:flex-end;">
              <button class="btn btn-outline" type="submit">Filter</button>
            </div>
          </div>
        </form>

        <!-- Toolbar -->
        <div class="toolbar" style="margin-bottom:12px;">
          <a class="btn btn-primary" href="tambah_harian.php">+ Tambah Laporan</a>
          <a class="btn btn-outline" href="export_laporan_harian_excel.php?bulan=<?=$bulan?>&tahun=<?=$tahun?>&minggu=<?=$minggu?>" target="_blank">Download Excel</a>
          <a class="btn btn-outline" href="export_laporan_harian_pdf.php?bulan=<?=$bulan?>&tahun=<?=$tahun?>&minggu=<?=$minggu?>" target="_blank">Download PDF</a>
        </div>

        <!-- Tabel -->
        <div class="table-wrap">
          <table>
            <tr>
              <th>HARI/TANGGAL</th>
              <th>NAMA UNIT</th>
              <th>KELUHAN/KERUSAKAN</th>
              <th>PENYEBAB</th>
              <th>TGL MULAI</th>
              <th>TGL SELESAI</th>
              <th>TINDAKAN PERBAIKAN</th>
              <th>DIINPUT OLEH</th>
              <th>AKSI</th>
            </tr>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= tgl_id($r['tgl']) ?></td>
                <td><?= htmlspecialchars($r['unit']) ?></td>
                <td><?= nl2br(htmlspecialchars($r['keluhan'])) ?></td>
                <td><?= nl2br(htmlspecialchars($r['penyebab'])) ?></td>
                <td><?= tgl_id($r['tgl_mulai']) ?></td>
                <td><?= tgl_id($r['tgl_selesai']) ?></td>
                <td><?= nl2br(htmlspecialchars($r['tindakan'])) ?></td>
                <td><?= htmlspecialchars($r['diinput_label']) ?></td>
                <td>
                  <a class="btn btn-warning" href="edit_harian.php?id=<?=$r['id']?>">Edit</a>
                  <a class="btn btn-danger" href="delete_harian.php?id=<?=$r['id']?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
              <tr><td colspan="9" style="text-align:center; color:#64748b;">Tidak ada data untuk kriteria ini.</td></tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
// auto-buka menu "Laporan"
document.addEventListener('DOMContentLoaded', function(){
  var menu = document.querySelector('#laporanMenu');
  if (menu) menu.classList.add('open');
  var chev = document.querySelector('.sidebar-collapse-trigger .chev');
  if (chev) chev.textContent = '▾';
});
</script>
</body>
</html>
