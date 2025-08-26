<form action="import_sparepart.php" method="post" enctype="multipart/form-data" style="margin:12px 0;">
  <input type="file" name="file_excel" accept=".xlsx,.xls" required>
  <button type="submit">Import Excel</button>
  <small style="margin-left:8px;">Gunakan file multi-sheet: setiap sheet = 1 unit (kecuali REKAP)</small>
</form>
