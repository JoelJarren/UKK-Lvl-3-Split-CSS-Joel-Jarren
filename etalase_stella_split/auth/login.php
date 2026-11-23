<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: ../index.php');
        exit();
    }
    $error = "Email atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="auth-page">

<div class="card card-login card-pad">
    <div class="auth-head">
        <img src="../assets/img/logo.png" alt="Logo Telkom Schools">
        <h3>Etalase Stella</h3>
        <p class="muted small">Silakan masuk ke akun Anda</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= icon('exclamation-triangle-fill') ?><?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Email</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('envelope') ?></span>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group lg">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('lock') ?></span>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-tall fw-semibold mb-3">Login <?= icon('arrow-right-short') ?></button>
    </form>

    <div class="auth-foot">
        <span class="muted small">Belum punya akun?</span>
        <a href="register.php" class="link-strong small">Daftar Siswa</a>
    </div>
</div>

</body>
</html>
