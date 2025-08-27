
<?php
require __DIR__ . '/../config/config.php';
if (!is_logged_in()) { header('Location: ../views/index.php'); exit; }

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: ../views/dashboard.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM laporan_perbaikan WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) { header('Location: ../views/dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_input = $_POST['tanggal_input'];
    $nama_unit = $_POST['nama_unit'];
    $keluhan = $_POST['keluhan_kerusakan'];
    $penyebab = $_POST['penyebab_kerusakan'];
    $tgl_mulai = $_POST['tgl_mulai_reparasi']?: null;
    $tgl_selesai = $_POST['tgl_selesai_reparasi']?: null;
    $tindakan = $_POST['tindakan_perbaikan'];

    $up = $pdo->prepare("UPDATE laporan_perbaikan SET tanggal_input=?, nama_unit=?, keluhan_kerusakan=?, penyebab_kerusakan=?, tgl_mulai_reparasi=?, tgl_selesai_reparasi=?, tindakan_perbaikan=? WHERE id = ?");
    $up->execute([$tanggal_input, $nama_unit, $keluhan, $penyebab, $tgl_mulai, $tgl_selesai, $tindakan, $id]);
  header('Location: ../views/dashboard.php');
    exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Edit Laporan - KCE Mekanik</title><link rel="icon" href="../assets/images/logo_kce_favicon.png"><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
  <div class="form-container">
    <h2 style="text-align:center; margin-bottom:32px;">Edit Laporan</h2>
    <form method="post">
      <div class="form-row">
        <div class="form-group">
          <label for="tanggal_input">Tanggal Pencatatan</label>
          <input type="date" name="tanggal_input" id="tanggal_input" value="<?=$data['tanggal_input']?>" required>
        </div>
        <div class="form-group">
          <label for="nama_unit">Nama Unit</label>
          <input type="text" name="nama_unit" id="nama_unit" value="<?=htmlspecialchars($data['nama_unit'])?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="keluhan_kerusakan">Keluhan/Kerusakan</label>
          <textarea name="keluhan_kerusakan" id="keluhan_kerusakan"><?=htmlspecialchars($data['keluhan_kerusakan'])?></textarea>
        </div>
        <div class="form-group">
          <label for="penyebab_kerusakan">Penyebab Kerusakan</label>
          <textarea name="penyebab_kerusakan" id="penyebab_kerusakan"><?=htmlspecialchars($data['penyebab_kerusakan'])?></textarea>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="tgl_mulai_reparasi">Tgl Mulai Reparasi</label>
          <input type="date" name="tgl_mulai_reparasi" id="tgl_mulai_reparasi" value="<?=$data['tgl_mulai_reparasi']?>">
        </div>
        <div class="form-group">
          <label for="tgl_selesai_reparasi">Tgl Selesai Reparasi</label>
          <input type="date" name="tgl_selesai_reparasi" id="tgl_selesai_reparasi" value="<?=$data['tgl_selesai_reparasi']?>">
        </div>
      </div>
      <div class="form-group">
        <label for="tindakan_perbaikan">Tindakan Perbaikan</label>
        <textarea name="tindakan_perbaikan" id="tindakan_perbaikan"><?=htmlspecialchars($data['tindakan_perbaikan'])?></textarea>
      </div>
      <button type="submit" class="btn btn-success" style="width:100%;margin-top:16px;">Simpan</button>
    </form>
  <p style="text-align:center;margin-top:24px;"><a href="../views/dashboard.php" class="btn btn-secondary">Kembali</a></p>
  </div>
</body></html>
