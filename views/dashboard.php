<?php
require __DIR__ . '/../config/config.php';
if (!is_logged_in()) { header('Location: index.php'); exit; }

// (opsional) aktifkan debug saat perlu:
// ini_set('display_errors',1); error_reporting(E_ALL);

// Nilai default jika query gagal
$todayCount = 0; $weekCount = 0; $monthCount = 0;
$hours = range(0,23); $hourCounts = array_fill(0,24,0);

// label 7 hari
$days7 = []; for ($i=6; $i>=0; $i--) { $days7[] = date('Y-m-d', strtotime("-$i day")); }
$dayCounts = array_fill_keys($days7, 0);

// label 5 minggu terakhir
$weeks = []; for ($i=4; $i>=0; $i--) { $weeks[date('o-W', strtotime("-$i week"))] = 0; }

try {
  // === STAT COUNTS ===
  $todayStmt = $pdo->query("SELECT COUNT(*) AS c FROM laporan_perbaikan WHERE tanggal_input = CURDATE()");
  if ($todayStmt) { $todayCount = (int)$todayStmt->fetch()['c']; }

  $weekStmt = $pdo->query("
    SELECT COUNT(*) AS c
    FROM laporan_perbaikan
    WHERE YEARWEEK(tanggal_input, 3) = YEARWEEK(CURDATE(), 3)
  ");
  if ($weekStmt) { $weekCount = (int)$weekStmt->fetch()['c']; }

  $monthStmt = $pdo->query("
    SELECT COUNT(*) AS c
    FROM laporan_perbaikan
    WHERE MONTH(tanggal_input) = MONTH(CURDATE()) AND YEAR(tanggal_input) = YEAR(CURDATE())
  ");
  if ($monthStmt) { $monthCount = (int)$monthStmt->fetch()['c']; }

  // 1) Hari ini per jam
  $hStmt = $pdo->query("
    SELECT HOUR(created_at) h, COUNT(*) c
    FROM laporan_perbaikan
    WHERE DATE(created_at) = CURDATE()
    GROUP BY HOUR(created_at)
  ");
  if ($hStmt) { foreach ($hStmt as $r) { $hourCounts[(int)$r['h']] = (int)$r['c']; } }

  // 2) 7 hari terakhir
  $q7 = $pdo->prepare("
    SELECT DATE(tanggal_input) d, COUNT(*) c
    FROM laporan_perbaikan
    WHERE tanggal_input BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
    GROUP BY DATE(tanggal_input)
  ");
  if ($q7 && $q7->execute()) { foreach ($q7 as $r) { $dayCounts[$r['d']] = (int)$r['c']; } }

  // 3) 5 minggu terakhir
  $qw = $pdo->query("
    SELECT DATE_FORMAT(tanggal_input, '%x-%v') w, COUNT(*) c
    FROM laporan_perbaikan
    WHERE tanggal_input >= DATE_SUB(CURDATE(), INTERVAL 35 DAY)
    GROUP BY DATE_FORMAT(tanggal_input, '%x-%v')
  ");
  if ($qw) { foreach ($qw as $r) { if (isset($weeks[$r['w']])) $weeks[$r['w']] = (int)$r['c']; } }

} catch (Throwable $e) {
  // biarkan default (0)
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - KCE Mekanik</title>
  <link rel="icon" href="../assets/images/logo_kce_transparent.png">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h1 style="margin:0 0 12px;">Dashboard</h1>

        <!-- Profil Perusahaan (card memanjang) -->
        <div class="card profile-card" style="margin-bottom:16px; width:100%;">
          <h3>Profil Perusahaan</h3>
          <div class="profile-text">
            PT Kalimantan Concrete Engineering (PT KCE) hadir untuk mengatasi permasalahan konstruksi pondasi di Kalimantan Selatan, Kalimantan Tengah, dan sekitarnya. Dengan produk unggulan 'tiang pancang beton prestress' yaitu tiang beton sistem prategang dengan mutu beton tinggi (high strength concrete) kekuatan beton lebih dari K-500 dengan berbagai ukuran tiang.<br><br>
            PT Kalimantan Concrete Engineering didirikan di Banjarbaru, Kalimantan Selatan pada tanggal 21 Desember 2009. Didukung oleh tenaga ahli dan pakar beton dari ITS Surabaya, PT Kalimantan Concrete Engineering mampu mengolah bahan baku lokal pilihan menjadi beton mutu tinggi dengan memanfaatkan perkembangan teknologi beton dan porses quality control yang berkesinambungan.<br><br>
            PT Kalimantan Concrete Engineering adalah perusahaan lokal banua (Kalimantan Selatan) yang bertujuan mensejahterakan anak anak banua dan berkarya memajukan Borneo, dengan produk ; Mini Pile (Tiang Pancang), U-Ditch, Box Culvert, dan Titian.
          </div>
        </div>

        <!-- 3 kartu statistik di bawah profil -->
        <div class="cards-grid" style="margin-bottom:16px;">
          <div class="card col-4">
            <h3>Laporan Hari Ini</h3>
            <div class="big-number"><?= $todayCount ?></div>
            <canvas id="chartToday" height="80"></canvas>
          </div>

          <div class="card col-4">
            <h3>Laporan Minggu Ini</h3>
            <div class="big-number"><?= $weekCount ?></div>
            <canvas id="chartWeek" height="80"></canvas>
          </div>

          <div class="card col-4">
            <h3>Laporan Bulan Ini</h3>
            <div class="big-number"><?= $monthCount ?></div>
            <canvas id="chartMonth" height="80"></canvas>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Data dari PHP → JS
    const hours = <?= json_encode(array_map(fn($h)=>sprintf("%02d:00",$h), $hours)) ?>;
    const hourCounts = <?= json_encode(array_values($hourCounts)) ?>;
    const days7Labels = <?= json_encode(array_map(fn($d)=>date("d/m", strtotime($d)), array_keys($dayCounts))) ?>;
    const days7Counts = <?= json_encode(array_values($dayCounts)) ?>;
    const weekLabels = <?= json_encode(array_keys($weeks)) ?>;
    const weekCounts = <?= json_encode(array_values($weeks)) ?>;

    // Chart Hari Ini (per jam)
    new Chart(document.getElementById('chartToday'), {
      type: 'bar',
      data: { labels: hours, datasets: [{ label: 'Hari ini', data: hourCounts }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { ticks: { maxTicksLimit: 12 }}, y: { beginAtZero: true } } }
    });

    // Chart 7 hari terakhir
    new Chart(document.getElementById('chartWeek'), {
      type: 'line',
      data: { labels: days7Labels, datasets: [{ label: '7 Hari Terakhir', data: days7Counts, tension: .3 }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    // Chart 5 minggu terakhir
    new Chart(document.getElementById('chartMonth'), {
      type: 'bar',
      data: { labels: weekLabels, datasets: [{ label: 'Per Minggu', data: weekCounts }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  </script>
</body>
</html>
