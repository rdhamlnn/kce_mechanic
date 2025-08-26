<?php
require 'config.php';
if (!is_logged_in() || !is_admin()) { header('Location: index.php'); exit; }

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM laporan_perbaikan WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: dashboard.php');
exit;
