<?php
require __DIR__ . '/../config/config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $role = $_POST['role'] ?? 'karyawan';

    if (!$username || !$password || !$nama) {
        $err = 'Semua field wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $err = 'Username sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)');
            $ins->execute([$username, $hash, $nama, $role]);
            header('Location: index.php?signup=success');
            exit;
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Buat Akun Baru - KCE Mekanik</title>
  <link rel="icon" href="../assets/images/logo_kce_favicon.png">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="login-card">
  <img src="../assets/images/logo_kce_transparent.png" alt="KCE" class="logo">
    <h2>Buat Akun Baru</h2>
    <?php if($err): ?><div class="alert"><?=$err?></div><?php endif; ?>
    <form method="post">
      <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
      <input type="text" name="username" placeholder="Username" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <select name="role" required style="width:100%;margin-bottom:12px;">
        <option value="karyawan">Karyawan</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit" class="btn btn-success">Daftar</button>
    </form>
  <p style="margin-top:16px;"><a href="index.php" style="color:#2563eb;text-decoration:underline;">Sudah punya akun? Login</a></p>
  </div>
</body>
</html>
