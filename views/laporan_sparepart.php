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
  <div class="card" style="padding:18px; background:#fff; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <h1 style="margin:0 0 4px; font-size:2rem; font-weight:700; color:#222;">Laporan Pemakaian Sparepart</h1>
        <div style="height:3px; width:60px; border-radius:3px; margin:8px 0 16px;"></div>

        <!-- Filter dalam card kecil -->
  <form method="get" class="card" style="padding:10px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:12px;">
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
              <button class="btn btn-outline" type="submit">Filter</button>
            </div>
            <div>
              <a href="laporan_sparepart.php" style="color:#ef4444; font-weight:600; text-decoration:none; display:inline-block; padding:8px 0 0 0;">Reset</a>
            </div>
          </div>
        </form>

        <!-- Toolbar -->
  <div class="toolbar" style="margin-bottom:12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; justify-content:flex-start;">
          <?php if (file_exists('../controllers/import_form.php')) include '../controllers/import_form.php'; ?>
          <?php if (file_exists('../controllers/sparepart_create.php')): ?>
            <a href="../controllers/sparepart_create.php" class="btn btn-primary">+ Tambah Data Manual</a>
          <?php endif; ?>
        </div>

        <!-- Tabel detail -->
        <div class="table-wrap">
          <table class="table" width="100%" style="border-radius:12px; overflow:hidden;">
            <tr style="background:#2563eb; color:#fff;">
              <th style="padding:12px 8px;">NO</th>
              <th style="padding:12px 8px;">TANGGAL</th>
              <th style="padding:12px 8px;">SPAREPART/MATERIAL/JASA</th>
              <th style="padding:12px 8px; text-align:center;">JUMLAH</th>
              <th style="padding:12px 8px;">BENTUK SATUAN</th>
              <th style="padding:12px 8px;">HARGA SATUAN</th>
              <th style="padding:12px 8px;">TOTAL HARGA</th>
              <th style="padding:12px 8px;">KETERANGAN</th>
              <th style="padding:12px 8px; text-align:center;">AKSI</th>
            </tr>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td style="padding:10px 8px; text-align:center; font-weight:500; color:#222; background:#fff;"><?=$r['no']?></td>
              <td style="padding:10px 8px; background:#fff;"><?=$r['tanggal_pakai']?></td>
              <td style="padding:10px 8px; background:#fff;"><?=htmlspecialchars($r['nama_item'])?></td>
              <td style="padding:10px 8px; text-align:center; vertical-align:middle; background:#fff;"><?=$r['jumlah']?></td>
              <td style="padding:10px 8px; background:#fff;"><?=$r['satuan']?></td>
              <td style="padding:10px 8px; background:#fff;"><?=number_format($r['harga_satuan'],0,',','.')?></td>
              <td style="padding:10px 8px; background:#fff;"><?=number_format($r['total_harga'],0,',','.')?></td>
              <td style="padding:10px 8px; background:#fff;"><?=htmlspecialchars($r['keterangan'])?></td>
              <td style="vertical-align:middle; text-align:center; background:#fff;">
                <div style="display:flex; gap:8px; justify-content:center; align-items:center;">
                  <?php if (file_exists('../controllers/sparepart_edit.php')): ?>
                      <a class="btn btn-warning btn-sm" href="../controllers/sparepart_edit.php?id=<?=$r['id']?>">Edit</a>
                  <?php endif; ?>
                  <?php if (file_exists('../controllers/sparepart_delete.php')): ?>
                      <a class="btn btn-danger btn-sm" href="../controllers/sparepart_delete.php?id=<?=$r['id']?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="4" style="text-align:center; font-weight:bold; background:#f3f4f6;">Jumlah</td>
              <td style="background:#f3f4f6; border-right:2px solid var(--border-color); padding:0;"></td>
              <td colspan="4" style="text-align:center; font-weight:bold; background:#f3f4f6; color:#2563eb; font-size:1.1rem;"><?=number_format($grand,0,',','.')?></td>
            </tr>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
</body>
</html>
