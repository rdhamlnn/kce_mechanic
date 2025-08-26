<?php
// partials/sidebar.php
if (!isset($_SESSION)) { session_start(); }
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="assets/images/logo_kce_transparent.png" alt="KCE" />
    <span>KCE Mechanic</span>
  </div>

  <nav class="sidebar-nav">
    <a class="sidebar-link <?= $current==='dashboard.php' ? 'active':'' ?>" href="dashboard.php">
      <span>🏠</span> <b>Dashboard</b>
    </a>

    <!-- COLLAPSIBLE: Laporan -->
    <button class="sidebar-link sidebar-collapse-trigger" type="button" data-target="#laporanMenu" style="padding-left:0.75em;">
      <span>📄</span> <b>Laporan</b>
      <span class="chev" aria-hidden="true">▸</span>
    </button>
    <div id="laporanMenu" class="sidebar-collapse">
      <a class="sidebar-sublink <?= $current==='laporan_harian_mekanik.php' ? 'active':'' ?>" href="laporan_harian_mekanik.php">
        • Laporan Harian Mekanik
      </a>
      <a class="sidebar-sublink <?= $current==='laporan_sparepart.php' ? 'active':'' ?>" href="laporan_sparepart.php">
        • Laporan Pemakaian Barang/Sparepart
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
