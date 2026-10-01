<?php
require 'config.php';
if (isset($_SESSION['role'])) { header('Location: '.($_SESSION['role']==='penjaga' ? 'penjaga.php' : 'warga.php')); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=?");
  $s->execute([trim($_POST['username']), hash('sha256', $_POST['password'])]);
  if ($u = $s->fetch()) { $_SESSION['role'] = $u['role']; header('Location: index.php'); exit; }
  $err = 'Username atau password salah.';
}
head('Login'); ?>
<div class="row justify-content-center"><div class="col-md-5">
  <?= stokInfo($pdo) ?>
  <div class="card shadow-sm"><div class="card-body">
    <h4 class="mb-3">Login</h4>
    <?php if ($err): ?><div class="alert alert-warning"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary w-100">Masuk</button>
    </form>
  </div></div>
</div></div>
<?php foot();
