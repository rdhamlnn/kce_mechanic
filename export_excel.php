<?php
require 'config.php';
if (!is_admin()) { header('Location: dashboard.php'); exit; }

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$monthName = strtoupper(date('F', mktime(0,0,0,intval($bulan),1))); // e.g. MAY (you can map to Indonesian if needed)

$stmt = $pdo->prepare("SELECT * FROM laporan_perbaikan WHERE MONTH(tanggal_input)=? AND YEAR(tanggal_input)=? ORDER BY tanggal_input ASC");
$stmt->execute([$bulan, $tahun]);
$data = $stmt->fetchAll();

function weekOfMonth($date) {
    $dt = new DateTime($date);
    $first = new DateTime($dt->format('Y-m-01'));
    $diff = (int)$dt->format('j') - 1;
    return intdiv($diff, 7) + 1; // 1..5
}

$weeks = [];
foreach ($data as $row) {
    $w = weekOfMonth($row['tanggal_input']);
    $weeks[$w][] = $row;
}

$spreadsheet = new Spreadsheet();

if(empty($weeks)){
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($monthName.' M1');
    $sheet->setCellValue('A1','Tidak ada data');
} else {
    $sheetIndex = 0;
    foreach ($weeks as $weekNo => $rows) {
        if ($sheetIndex == 0) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($monthName.' M'.$weekNo);
        } else {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($monthName.' M'.$weekNo);
        }
        $sheet->setCellValue('A1', 'PT. Kalimantan Concrete Engineering');
        $sheet->setCellValue('A2', 'LAPORAN PERBAIKAN MEKANIK');
        $sheet->setCellValue('A3', 'PERIODE: '.$monthName.' '.$tahun);

        $head = ['Hari/Tanggal','Nama Unit','Keluhan/Kerusakan','Penyebab Kerusakan','Tgl Mulai Reparasi','Tgl Selesai Reparasi','Tindakan Perbaikan'];
        $rowNum = 5;
        $col = 'A';
        foreach ($head as $h) {
            $sheet->setCellValue($col.$rowNum, $h);
            $col++;
        }
        $rowNum++;
        foreach ($rows as $r) {
            $sheet->setCellValue('A'.$rowNum, date('d/m/Y', strtotime($r['tanggal_input'])));
            $sheet->setCellValue('B'.$rowNum, $r['nama_unit']);
            $sheet->setCellValue('C'.$rowNum, $r['keluhan_kerusakan']);
            $sheet->setCellValue('D'.$rowNum, $r['penyebab_kerusakan']);
            $sheet->setCellValue('E'.$rowNum, !empty($r['tgl_mulai_reparasi'])?date('d/m/Y', strtotime($r['tgl_mulai_reparasi'])):'');
            $sheet->setCellValue('F'.$rowNum, !empty($r['tgl_selesai_reparasi'])?date('d/m/Y', strtotime($r['tgl_selesai_reparasi'])):'');
            $sheet->setCellValue('G'.$rowNum, $r['tindakan_perbaikan']);
            $rowNum++;
        }
        $sheetIndex++;
    }
}

$filename = 'Laporan_'.$monthName.'_'.$tahun.'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
