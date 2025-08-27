<?php
require __DIR__ . '/../config/config.php';
if (!is_logged_in()) { header('Location: ../views/index.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ../views/laporan_sparepart.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tanggal = $_POST['tanggal_pakai'] ?? '';
  $unit = trim($_POST['unit'] ?? '');
  $nama = trim($_POST['nama_item'] ?? '');
  $jumlah = (float)($_POST['jumlah'] ?? 0);
  $satuan = trim($_POST['satuan'] ?? '');
  $harga = (float)($_POST['harga_satuan'] ?? 0);
  $ket = trim($_POST['keterangan'] ?? '');

  if ($tanggal && $unit && $nama) {
    $stmt = $pdo->prepare("UPDATE laporan_sparepart
      SET tanggal_pakai=?, unit=?, nama_item=?, jumlah=?, satuan=?, harga_satuan=?, keterangan=?
      WHERE id=?");
    $stmt->execute([$tanggal, $unit, $nama, $jumlah, $satuan, $harga, $ket, $id]);
  header('Location: ../views/laporan_sparepart.php');
    exit;
  } else {
    $err = "Tanggal, Unit, dan Nama Item wajib diisi.";
  }
}

// ambil data
$st = $pdo->prepare("SELECT * FROM laporan_sparepart WHERE id=?");
$st->execute([$id]);
$data = $st->fetch();
if (!$data) { echo "Data tidak ditemukan"; exit; }
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Sparepart</title><link rel="stylesheet" href="../assets/css/style.css"></head>

<body>
  <div class="form-container">
    <h2 style="text-align:center; margin-bottom:32px;">Edit Data Sparepart</h2>
    <?php if (!empty($err)) echo "<div class='card' style='color:red;'>$err</div>"; ?>
    <form method="post">
      <div class="form-row">
        <div class="form-group">
          <label for="tanggal_pakai">Tanggal Pakai</label>
          <input type="date" name="tanggal_pakai" id="tanggal_pakai" value="<?=$data['tanggal_pakai']?>" required>
        </div>
        <div class="form-group">
          <label for="unit">Unit</label>
          <input type="text" name="unit" id="unit" value="<?=htmlspecialchars($data['unit'])?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="nama_item">Sparepart/Material/Jasa</label>
          <input type="text" name="nama_item" id="nama_item" value="<?=htmlspecialchars($data['nama_item'])?>" required>
        </div>
        <div class="form-group">
          <label for="jumlah">Jumlah</label>
          <input type="number" step="0.01" name="jumlah" id="jumlah" value="<?=$data['jumlah']?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="satuan">Bentuk Satuan</label>
          <input type="text" name="satuan" id="satuan" value="<?=htmlspecialchars($data['satuan'])?>" required>
        </div>
        <div class="form-group">
          <label for="harga_satuan">Harga Satuan</label>
          <input type="number" step="0.01" name="harga_satuan" id="harga_satuan" value="<?=$data['harga_satuan']?>" required>
        </div>
      </div>
      <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="3"><?=htmlspecialchars($data['keterangan'])?></textarea>
      </div>
      <button type="submit" class="btn btn-success" style="width:100%;margin-top:16px;">Update</button>
    </form>
  <p style="text-align:center;margin-top:24px;"><a href="../views/laporan_sparepart.php" class="btn btn-secondary">Kembali</a></p>
  </div>
</body>
</html>
