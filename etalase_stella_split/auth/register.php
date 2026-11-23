<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($nama === '' || $email === '' || $password === '') {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        try {
            // Pendaftaran publik selalu berperan 'siswa'
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'siswa')");
            $stmt->execute([
                ':nama'     => $nama,
                ':email'    => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $success = "Pendaftaran berhasil! Silakan login.";
        } catch (PDOException $ex) {
            $error = is_duplicate_error($ex)
                ? "Email sudah terdaftar, silakan gunakan email lain atau login."
                : "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="auth-page">

<div class="card card-login card-pad" style="margin: 1.5rem 0;">
    <div class="auth-head">
        <img src="../assets/img/logo.png" alt="Logo Telkom Schools">
        <h3>Registrasi Siswa</h3>
        <p class="muted small">Buat akun siswa untuk memamerkan karya Anda</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= icon('exclamation-triangle-fill') ?><?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <?= icon('check-circle-fill') ?><?= e($success) ?>
            <div class="mt-2">
                <a href="login.php" class="btn btn-sm btn-success btn-block">Ke Halaman Login</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('card-heading') ?></span>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="<?= e($_POST['nama'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('envelope') ?></span>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email aktif" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('lock') ?></span>
                <input type="password" name="password" class="form-control" placeholder="Buat password (min. 6 karakter)" minlength="6" required>
            </div>
        </div>
        <div class="form-group lg">
            <label class="form-label">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-icon"><?= icon('shield-lock') ?></span>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-tall fw-semibold mb-3">Daftar Sekarang <?= icon('arrow-right-short') ?></button>
    </form>
    <?php endif; ?>

    <div class="auth-foot">
        <span class="muted small">Sudah punya akun?</span>
        <a href="login.php" class="link-strong small">Login di sini</a>
    </div>
</div>

</body>
</html>
