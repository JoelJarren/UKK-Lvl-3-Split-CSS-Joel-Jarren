<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';
require_once '../includes/helpers.php';

// Hanya Super Admin yang boleh membuka halaman ini
check_role(['super_admin'], '../');

$error       = '';
$openPopup   = false;   // buka kembali popup jika ada error validasi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah_admin') {
        $nama     = trim($_POST['nama'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nama === '' || $email === '' || $password === '') {
            $error = "Semua kolom wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, 'admin')");
                $stmt->execute([
                    ':nama'  => $nama,
                    ':email' => $email,
                    ':pass'  => password_hash($password, PASSWORD_DEFAULT),
                ]);
                set_flash('success', 'Admin baru berhasil ditambahkan.');
                header('Location: users.php');
                exit();
            } catch (PDOException $ex) {
                $error = is_duplicate_error($ex) ? "Email sudah terdaftar!" : "Gagal menyimpan data admin.";
            }
        }
        $openPopup = true;
    } elseif ($action === 'hapus') {
        // Super Admin tidak bisa dihapus; admin & siswa bisa
        if (delete_user_with_files($pdo, (int) ($_POST['id'] ?? 0), ['admin', 'siswa'])) {
            set_flash('success', 'User berhasil dihapus.');
        }
        header('Location: users.php');
        exit();
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY FIELD(role, 'super_admin', 'admin', 'siswa'), nama ASC")->fetchAll();

$base   = '../';
$active = 'users';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Super Admin</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/kelola.css">
    <link rel="stylesheet" href="../assets/css/popup.css">
</head>
<body>

<?php require __DIR__ . '/../includes/navbar.php'; ?>

<div class="container page-sm">
    <?= render_flash() ?>

    <div class="page-head compact">
        <div>
            <h3 class="fw-bold mb-0">Manajemen User</h3>
            <p class="muted mb-0 small">Khusus Super Admin &ndash; tambah admin baru dan kelola seluruh akun.</p>
        </div>
        <button type="button" class="btn btn-primary btn-pill" data-popup-open="popupTambahAdmin">
            <?= icon('person-plus-fill', 'mr-1') ?> Tambah Admin
        </button>
    </div>

    <div class="card card-custom card-pad">
        <div class="table-wrap">
            <table class="table flush">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($users as $u): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><b><?= e($u['nama']) ?></b></td>
                        <td><code><?= e($u['email']) ?></code></td>
                        <td><span class="badge-role badge-<?= e($u['role']) ?>"><?= e(str_replace('_', ' ', $u['role'])) ?></span></td>
                        <td>
                            <?php if ($u['role'] !== 'super_admin'): ?>
                                <form method="POST" onsubmit="return confirm('Hapus user ini beserta karya yang diunggah?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><?= icon('trash') ?> Hapus</button>
                                </form>
                            <?php else: ?>
                                <span class="muted">&ndash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Popup Tambah Admin -->
<div id="popupTambahAdmin" class="popup-overlay" aria-hidden="true">
    <div class="popup-content" role="dialog" aria-modal="true" aria-labelledby="popupAdminTitle">
        <button type="button" class="popup-close" data-popup-close aria-label="Tutup">&times;</button>
        <div class="popup-body">
            <h2 id="popupAdminTitle" class="popup-title"><?= icon('person-plus-fill', 'text-primary') ?> Tambah Admin Baru</h2>
            <p class="popup-meta mb-3">Akun baru akan dibuat dengan peran <b>Admin</b>.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= icon('exclamation-triangle-fill') ?><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah_admin">
                <div class="form-group">
                    <label class="form-label fw-semibold">Nama Admin</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Admin" value="<?= e($_POST['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label fw-semibold">Email Admin</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@stella.sch.id" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group lg">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn btn-primary btn-grow fw-semibold"><?= icon('check-lg') ?> Simpan Admin</button>
                    <button type="button" class="btn btn-outline-secondary" data-popup-close>Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/popup.js"></script>
<?php if ($openPopup): ?>
<script>StellaPopup.open(document.getElementById('popupTambahAdmin'));</script>
<?php endif; ?>
</body>
</html>
