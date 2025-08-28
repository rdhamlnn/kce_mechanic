<?php
// partials/sidebar.php
if (!isset($_SESSION)) { session_start(); }
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
  <img src="../assets/images/logo_kce_transparent.png" alt="KCE" />
    <span>KCE Mechanic</span>
  </div>

  <nav class="sidebar-nav">
    <a class="sidebar-link sidebar-tab <?= $current==='dashboard.php' ? 'active':'' ?>" href="dashboard.php" data-label="Dashboard">
      <span>🏠</span> <span><b>Dashboard</b></span>
    </a>

    <!-- COLLAPSIBLE: Laporan -->
    <button class="sidebar-link sidebar-collapse-trigger" type="button" data-target="#laporanMenu" style="padding-left:0.75em;">
  <span>📄</span> <span><b>Laporan</b></span>
  <span class="chev" aria-hidden="true">▸</span>
    </button>
    <div id="laporanMenu" class="sidebar-collapse">
      <a class="sidebar-sublink sidebar-tab <?= $current==='laporan_harian_mekanik.php' ? 'active':'' ?>" href="laporan_harian_mekanik.php" data-label="Laporan Harian Mekanik">
        <span>🧑🏻‍🔧</span> <span>Laporan Harian Mekanik</span>
      </a>
      <a class="sidebar-sublink sidebar-tab <?= $current==='laporan_sparepart.php' ? 'active':'' ?>" href="laporan_sparepart.php" data-label="Laporan Pemakaian Barang/Sparepart">
        <span>⚙️</span> <span>Laporan Pemakaian Barang/Sparepart</span>
      </a>
    </div>
  </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var current = "<?= $current ?>";
  var laporanPages = ["laporan_harian_mekanik.php", "laporan_sparepart.php"];
  var menu = document.querySelector('#laporanMenu');
  var chev = document.querySelector('.sidebar-collapse-trigger .chev');
  if (menu && laporanPages.includes(current)) {
    menu.classList.add('open');
    if (chev) chev.textContent = '▾';
  }
  document.querySelectorAll('.sidebar-collapse-trigger').forEach(function(btn){
    btn.addEventListener('click', function(){
      var target = document.querySelector(btn.getAttribute('data-target'));
      if (!target) return;
      var open = target.classList.toggle('open');
      var chev = btn.querySelector('.chev');
      if (chev) chev.textContent = open ? '▾' : '▸';
    });
  });
});
</script>
