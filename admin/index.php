<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

check_role(['super_admin', 'admin']);

$total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$total_siswa    = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'siswa'")->fetchColumn();
$total_admin    = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'super_admin')")->fetchColumn();

$terbaru = $pdo->query("SELECT projects.nama_project, projects.created_at, users.nama AS pembuat
                        FROM projects
                        JOIN users ON projects.user_id = users.id
                        ORDER BY projects.created_at DESC
                        LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<nav class="navbar">
    <h2>Dashboard Admin</h2>
    <div class="navbar-user">
        <a href="../index.php" class="btn btn-secondary">Etalase</a>
        <?php if ($_SESSION['role'] === 'super_admin'): ?>
            <a href="users.php" class="btn btn-warning">Kelola Users</a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="admin-container">
    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-number"><?= (int) $total_projects ?></span>
            <span class="stat-label">Total Karya</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= (int) $total_siswa ?></span>
            <span class="stat-label">Siswa</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= (int) $total_admin ?></span>
            <span class="stat-label">Admin</span>
        </div>
    </div>

    <div class="table-container">
        <h3>Karya Terbaru</h3>
        <table class="custom-table">
            <tr>
                <th>Nama Karya</th><th>Pembuat</th><th>Tanggal</th>
            </tr>
            <?php foreach ($terbaru as $t): ?>
            <tr>
                <td><?= e($t['nama_project']) ?></td>
                <td><?= e($t['pembuat']) ?></td>
                <td><?= e(date('d M Y', strtotime($t['created_at']))) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$terbaru): ?>
            <tr><td colspan="3">Belum ada karya.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</main>
</body>
</html>
