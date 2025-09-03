<?php
require __DIR__ . '/../config/config.php';
if (!is_logged_in()) { header('Location: index.php'); exit; }

// FILTER tanggal (sesuai UI laporan mekanik)
$where = [];
$params = [];
if (!empty($_GET['dari']) && !empty($_GET['sampai'])) {
  $where[] = "tanggal_pakai BETWEEN ? AND ?";
  $params[] = $_GET['dari'];
  $params[] = $_GET['sampai'];
}

// DATA TABEL (tanpa kolom UNIT di tampilan; gunakan DENSE_RANK utk "No per Tanggal")
$sql = "SELECT id,
               DENSE_RANK() OVER (ORDER BY tanggal_pakai) AS no,
               tanggal_pakai,
               nama_item,
               jumlah,
               satuan,
               harga_satuan,
               total_harga,
               keterangan
        FROM laporan_sparepart";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY tanggal_pakai, id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// GRAND total
$grand = 0; foreach ($rows as $r) { $grand += (float)$r['total_harga']; }

// REKAP total (opsional, kalau mau tampilkan rekap global di bawah)
$rekapTotal = $grand;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laporan Pemakaian Sparepart</title>
  <link rel="icon" href="../assets/images/logo_kce_transparent.png">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '../partials/sidebar.php'; ?>
  <div>
    <header class="header">
  <img src="../assets/images/logo_kce_transparent.png" alt="KCE" class="logo-header">
      <div class="user-info">
        Halo, <?=htmlspecialchars($_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'user')?> |
  <a href="logout.php">Logout</a>
      </div>
    </header>

    <main class="main">
      <h1 style="margin:0 0 12px;">Laporan Pemakaian Sparepart</h1>

      <!-- Filter (tanggal) -->
  <form method="get" class="card" style="padding:10px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:12px;">
        <div class="filter-box" style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
          <div>
            <label>Dari:</label><br>
            <input type="date" name="dari" value="<?=htmlspecialchars($_GET['dari'] ?? '')?>" class="filter-input" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px;">
          </div>
          <div>
            <label>Sampai:</label><br>
            <input type="date" name="sampai" value="<?=htmlspecialchars($_GET['sampai'] ?? '')?>" class="filter-input" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px;">
          </div>
          <div style="align-self:flex-end;">
            <button class="btn btn-outline" type="submit" style="background:#fff; color:#1f2937; border:1px solid #cbd5e1; font-weight:700;">Filter</button>
          </div>
          <div>
            <a href="laporan_sparepart.php" style="color:#ef4444; font-weight:600; text-decoration:none; display:inline-block; padding:8px 0 0 0;">Reset</a>
          </div>
        </div>
      </form>

      <!-- Import Excel + Tambah manual (opsional jika sudah kamu punya file2nya) -->
      <?php if (file_exists('../controllers/import_form.php')) include '../controllers/import_form.php'; ?>
      <?php if (file_exists('../controllers/sparepart_create.php')): ?>
        <div style="margin:8px 0;">
          <a href="../controllers/sparepart_create.php" class="btn">+ Tambah Data Manual</a>
        </div>
      <?php endif; ?>

      <!-- Tabel detail (UI meniru laporan mekanik) -->
      <div class="table-wrap" id="tab-detail">
        <table class="table" width="100%">
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Sparepart/Material/Jasa</th>
            <th style="text-align:center;">Jumlah</th>
            <th>Bentuk Satuan</th>
            <th>Harga Satuan</th>
            <th>Total Harga</th>
            <th>Keterangan</th>
            <th style="text-align:center;">Aksi</th>
          </tr>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=$r['no']?></td>
            <td><?=$r['tanggal_pakai']?></td>
            <td><?=htmlspecialchars($r['nama_item'])?></td>
            <td style="text-align:center; vertical-align:middle;"><?=$r['jumlah']?></td>
            <td><?=$r['satuan']?></td>
            <td><?=number_format($r['harga_satuan'],0,',','.')?></td>
            <td><?=number_format($r['total_harga'],0,',','.')?></td>
            <td><?=htmlspecialchars($r['keterangan'])?></td>
            <td style="vertical-align:middle;">
              <?php if (file_exists('sparepart_edit.php')): ?>
                  <a class="btn btn-warning" href="../controllers/sparepart_edit.php?id=<?=$r['id']?>">Edit</a>
              <?php endif; ?>
              <?php if (file_exists('sparepart_delete.php')): ?>
                  <a class="btn btn-danger" href="../controllers/sparepart_delete.php?id=<?=$r['id']?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="4" style="text-align:center; font-weight:bold;">Jumlah</td>
            <td style="border-right:2px solid var(--border-color); padding:0;"></td>
            <td colspan="4" style="text-align:center; font-weight:bold;"><?=number_format($grand,0,',','.')?></td>
          </tr>
        </table>
      </div>
    </main>
  </div>
</div>
</body>
</html>
