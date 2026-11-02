<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$stmt = $pdo->query("SELECT projects.*, users.nama AS pembuat
                     FROM projects
                     JOIN users ON projects.user_id = users.id
                     ORDER BY projects.created_at DESC");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<nav class="navbar">
    <h2>Etalase Digital Stella</h2>
    <div class="navbar-user">
        <span>Halo, <b><?= e($_SESSION['nama']) ?></b>
            <span class="badge-role badge-<?= e($_SESSION['role']) ?>"><?= e($_SESSION['role']) ?></span>
        </span>
        <a href="project_tambah.php" class="btn btn-primary">+ Tambah Karya</a>
        <?php if (in_array($_SESSION['role'], ['super_admin', 'admin'], true)): ?>
            <a href="admin/index.php" class="btn btn-secondary">Dashboard</a>
        <?php endif; ?>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="admin/users.php" class="btn btn-warning">Kelola Users</a>
        <?php endif; ?>
        <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="container">
    <?php if (!$projects): ?>
        <p class="empty-state">Belum ada karya yang diunggah.</p>
    <?php endif; ?>

    <div class="grid-projects">
        <?php foreach ($projects as $p): ?>
            <article class="card-project">
                <img class="card-img-top" src="uploads/<?= e($p['gambar']) ?>" alt="<?= e($p['nama_project']) ?>">
                <div class="card-body">
                    <h3 class="card-title"><?= e($p['nama_project']) ?></h3>
                    <p class="card-author">Oleh: <b><?= e($p['pembuat']) ?></b></p>
                    <p class="card-text"><?= e($p['deskripsi']) ?></p>
                    <a class="card-link" href="<?= e($p['link_project']) ?>" target="_blank" rel="noopener noreferrer">Lihat project &rarr;</a>

                    <?php if (can_manage($p)): ?>
                        <div class="card-actions">
                            <a href="project_edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-warning">Edit</a>
                            <a href="project_hapus.php?id=<?= (int) $p['id'] ?>" class="btn btn-danger">Hapus</a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
