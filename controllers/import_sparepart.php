<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!is_logged_in()) { header('Location: ../views/index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_excel'])) {
    $tmp = $_FILES['file_excel']['tmp_name'];
    if (!file_exists($tmp)) { die('File tidak ditemukan'); }

    $spreadsheet = IOFactory::load($tmp);
    $pdo->beginTransaction();
    try {
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (strtoupper(trim($sheetName)) === 'REKAP') continue; // skip rekap

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;
            $rows = $sheet->toArray(null, true, true, true);

            // cari baris header (yang mengandung "Tanggal")
            $headerRow = null;
            foreach ($rows as $i => $row) {
                $joined = strtolower(implode(' ', array_map('strval', $row)));
                if (strpos($joined, 'tanggal') !== false && (strpos($joined, 'sparepart') !== false || strpos($joined, 'material') !== false || strpos($joined, 'jasa') !== false)) {
                    $headerRow = $i;
                    break;
                }
            }
            if (!$headerRow) continue;

            for ($i = $headerRow + 1; $i <= count($rows); $i++) {
                $r = $rows[$i] ?? null; if (!$r) continue;

                $tanggal      = trim($r['B'] ?? '');
                $nama_item    = trim($r['C'] ?? '');
                $jumlah       = (float) str_replace([','], ['.'], $r['D'] ?? 0);
                $satuan       = trim($r['E'] ?? '');
                $harga_satuan = (float) str_replace([','], ['.'], $r['F'] ?? 0);
                $keterangan   = trim($r['H'] ?? '');

                if (!$tanggal || !$nama_item) continue;

                // Normalisasi tanggal
                $tgl = date('Y-m-d', strtotime($tanggal));

                // Jika DB tanpa generated column, hitung manual total_harga = jumlah * harga_satuan
                if (true) {
                    $stmt = $pdo->prepare("INSERT INTO laporan_sparepart
                        (tanggal_pakai, unit, nama_item, jumlah, satuan, harga_satuan, keterangan, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$tgl, $sheetName, $nama_item, $jumlah, $satuan, $harga_satuan, $keterangan, $_SESSION['user']['id'] ?? null]);
                }
            }
        }
        $pdo->commit();
        echo "<script>alert('Import selesai!');window.location='../views/laporan_sparepart.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Gagal import: ".$e->getMessage();
    }
} else {
    header('Location: ../views/laporan_sparepart.php');
}
