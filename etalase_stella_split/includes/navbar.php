<?php
// Variabel dari halaman pemanggil: $base ('' atau '../'), $active ('galeri' | 'siswa' | 'users' | 'leaderboard')
$base   = $base ?? '';
$active = $active ?? '';
?>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= $base ?>index.php">
            <img src="<?= $base ?>assets/img/logo.png" alt="Logo">
            <span>Etalase Stella</span>
        </a>
        <button class="nav-toggle" type="button" id="navToggle" aria-label="Buka menu" aria-controls="navMenu" aria-expanded="false">
            <svg viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h22M4 15h22M4 23h22"/></svg>
        </button>
        <div class="navbar-menu" id="navMenu">
            <ul class="nav-list">
                <li>
                    <a class="nav-link <?= $active === 'galeri' ? 'active' : '' ?>" href="<?= $base ?>index.php"><?= icon('grid-fill') ?> Galeri Karya</a>
                </li>
                <?php if (is_staff()): ?>
                    <li>
                        <a class="nav-link <?= $active === 'siswa' ? 'active' : '' ?>" href="<?= $base ?>admin/siswa.php"><?= icon('people-fill') ?> Kelola Siswa</a>
                    </li>
                <?php endif; ?>
                <?php if (is_super_admin()): ?>
                    <li>
                        <a class="nav-link <?= $active === 'users' ? 'active' : '' ?>" href="<?= $base ?>admin/users.php"><?= icon('shield-lock-fill') ?> Kelola User</a>
                    </li>
                <?php endif; ?>
                <li>
                    <a class="nav-link <?= $active === 'leaderboard' ? 'active' : '' ?>" href="<?= $base ?>leaderboard.php"><?= icon('trophy-fill') ?> Leaderboard</a>
                </li>
            </ul>
            <div class="navbar-user">
                <span>Halo, <b><?= e($_SESSION['nama']) ?></b> <span class="badge"><?= e(str_replace('_', ' ', $_SESSION['role'])) ?></span></span>
                <a href="<?= $base ?>auth/logout.php" class="btn btn-outline-danger btn-sm"><?= icon('box-arrow-right') ?> Logout</a>
            </div>
        </div>
    </div>
</nav>
<script>
(function () {
    var btn = document.getElementById('navToggle');
    var menu = document.getElementById('navMenu');
    if (!btn || !menu) { return; }
    btn.addEventListener('click', function () {
        var open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
})();
</script>
