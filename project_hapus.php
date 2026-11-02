<?php
require_once 'config/kuis_db.php';
require_once 'config/upload.php';
require_once 'middleware/auth_check.php';

check_login();

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
$stmt->execute([':id' => $id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: index.php');
    exit();
}

if (!can_manage($project)) {
    http_response_code(403);
    die("Akses Ditolak: Anda tidak dapat menghapus karya milik siswa lain.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    hapus_gambar($project['gambar']);

    $del = $pdo->prepare("DELETE FROM projects WHERE id = :id");
    $del->execute([':id' => $id]);

    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-container">
    <h2>Hapus Karya</h2>
    <p class="confirm-text">Yakin ingin menghapus karya <b><?= e($project['nama_project']) ?></b>? Gambar dan datanya akan hilang permanen.</p>
    <form method="POST" class="form-actions">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
