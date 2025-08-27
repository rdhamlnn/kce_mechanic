<?php
require __DIR__ . '/../config/config.php';
if (!is_logged_in() || !is_admin()) { header('Location: ../views/index.php'); exit; }

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM laporan_perbaikan WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: ../views/dashboard.php');
exit;
