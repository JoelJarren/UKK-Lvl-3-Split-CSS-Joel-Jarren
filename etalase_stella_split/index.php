<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare("SELECT projects.*, users.nama AS pembuat
                           FROM projects
                           JOIN users ON projects.user_id = users.id
                           WHERE projects.nama_project LIKE :k1 OR users.nama LIKE :k2
                           ORDER BY projects.created_at DESC, projects.id DESC");
    $stmt->execute([':k1' => "%$cari%", ':k2' => "%$cari%"]);
} else {
    $stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat
                         FROM projects
                         JOIN users ON projects.user_id = users.id
                         ORDER BY projects.created_at DESC, projects.id DESC");
}
$projects = $stmt->fetchAll();

$base   = '';
$active = 'galeri';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/popup.css">
</head>
<body>

<?php require __DIR__ . '/includes/navbar.php'; ?>

<div class="container page">
    <?= render_flash() ?>

    <div class="page-head">
        <div>
            <h2 class="gallery-title">Galeri Project Siswa</h2>
            <p class="muted mb-0">Kumpulan karya dan pameran digital siswa Stella</p>
        </div>
        <form method="GET" action="index.php" class="search-form">
            <div class="input-group">
                <span class="input-icon white"><?= icon('search', 'muted') ?></span>
                <input type="text" name="cari" class="form-control" placeholder="cari karya/nama project" value="<?= e($cari) ?>">
                <?php if ($cari !== ''): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset"><?= icon('x-lg') ?></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="project_tambah.php" class="btn btn-success btn-pill nowrap"><?= icon('plus-lg', 'mr-1') ?> Tambah Project</a>
        </form>
    </div>

    <div class="grid-cards">
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $p): ?>
                <?php $canManage = is_staff() || $_SESSION['user_id'] == $p['user_id']; ?>
                <!-- Klik kartu => popup detail (data dibaca oleh JS di bawah) -->
                <div class="card project-card" tabindex="0" role="button"
                     data-id="<?= (int) $p['id'] ?>"
                     data-judul="<?= e($p['nama_project']) ?>"
                     data-pembuat="<?= e($p['pembuat']) ?>"
                     data-deskripsi="<?= e($p['deskripsi']) ?>"
                     data-link="<?= e($p['link_project']) ?>"
                     data-gambar="uploads/<?= e($p['gambar']) ?>"
                     data-tanggal="<?= e(date('d M Y', strtotime($p['created_at']))) ?>"
                     data-manage="<?= $canManage ? '1' : '0' ?>">
                    <img src="uploads/<?= e($p['gambar']) ?>" class="project-img" alt="Gambar Project">
                    <div class="project-body">
                        <h5 class="project-title"><?= e($p['nama_project']) ?></h5>
                        <p class="muted small mb-2"><?= icon('person-circle') ?> Oleh: <b><?= e($p['pembuat']) ?></b></p>
                        <p class="project-desc"><?= e(mb_strimwidth($p['deskripsi'], 0, 120, '...')) ?></p>
                        <div class="project-more">
                            <?= icon('arrows-fullscreen') ?> Klik untuk lihat detail
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <?= icon('folder-x') ?>
                <p class="muted mt-2">Tidak ada project atau karya yang ditemukan.</p>
                <?php if ($cari !== ''): ?>
                    <a href="index.php" class="btn btn-sm btn-outline-primary mt-2">Reset Pencarian</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Popup detail project -->
<div id="projectPopup" class="popup-overlay" aria-hidden="true">
    <div class="popup-content" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button type="button" class="popup-close" data-popup-close aria-label="Tutup">&times;</button>
        <img id="popupImg" class="popup-img" src="" alt="Detail Karya">
        <div class="popup-body">
            <h2 id="popupTitle" class="popup-title"></h2>
            <div id="popupAuthor" class="popup-meta"></div>
            <div id="popupDate" class="popup-meta"></div>
            <p id="popupDesc" class="popup-desc"></p>
            <a id="popupLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-block fw-semibold">Buka Tautan Project &rarr;</a>

            <div id="popupActions" class="popup-actions" style="display: none;">
                <a id="popupEdit" href="#" class="btn btn-warning btn-sm fw-semibold"><?= icon('pencil-square') ?> Edit</a>
                <form id="popupDeleteForm" method="POST" action="project_hapus.php" onsubmit="return confirm('Hapus karya ini?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="popupDeleteId" value="">
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold"><?= icon('trash') ?> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/popup.js"></script>
<script>
(function () {
    var popup = document.getElementById('projectPopup');

    function openProject(card) {
        var d = card.dataset;

        document.getElementById('popupImg').src = d.gambar;
        document.getElementById('popupTitle').textContent = d.judul;
        document.getElementById('popupDesc').textContent = d.deskripsi;

        var author = document.getElementById('popupAuthor');
        author.textContent = 'Oleh: ';
        var strong = document.createElement('b');
        strong.textContent = d.pembuat;
        author.appendChild(strong);

        document.getElementById('popupDate').textContent = 'Dibuat pada: ' + d.tanggal;
        document.getElementById('popupLink').href = d.link;

        var actions = document.getElementById('popupActions');
        if (d.manage === '1') {
            document.getElementById('popupEdit').href = 'project_edit.php?id=' + encodeURIComponent(d.id);
            document.getElementById('popupDeleteId').value = d.id;
            actions.style.display = 'flex';
        } else {
            actions.style.display = 'none';
        }

        StellaPopup.open(popup);
    }

    document.querySelectorAll('.project-card').forEach(function (card) {
        card.addEventListener('click', function () { openProject(card); });
        card.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                openProject(card);
            }
        });
    });
})();
</script>

</body>
</html>
