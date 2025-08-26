<?php
require 'config.php';
if (!is_logged_in()) { header('Location: index.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $stmt = $pdo->prepare("DELETE FROM laporan_sparepart WHERE id=?");
  $stmt->execute([$id]);
}
header('Location: laporan_sparepart.php');
