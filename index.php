<?php
require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $err = 'Username atau password salah';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - KCE Mekanik</title>
  <link rel="icon" href="assets/images/logo_kce_favicon.png">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="login-wrapper">
    <div class="login-card">
      <img src="assets/images/logo_kce_transparent.png" alt="KCE" class="logo">
      <h2>LOGIN</h2> 
      <h2>Sistem Laporan Mekanik</h2>
      <?php if($err): ?><div class="alert"><?=$err?></div><?php endif; ?>
      <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" style="background: #007bff; color: #fff; border: none; padding: 10px 30px; border-radius: 5px; font-size: 16px; cursor: pointer; transition: background 0.2s;">
          Login
        </button>
  </form>
  <p style="margin-top:16px;">Belum punya akun? <a href="signup.php" style="color:#2563eb;text-decoration:underline;">Buat akun baru</a></p>
    <!-- <p>Demo: admin / admin123  |  karyawan / karyawan123</p> -->
    </div>
  </div>
</body>
</html>
