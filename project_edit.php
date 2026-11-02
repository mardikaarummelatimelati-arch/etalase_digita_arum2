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
    die("Data karya tidak ditemukan.");
}

if (!can_manage($project)) {
    http_response_code(403);
    die("Akses Ditolak: Anda tidak dapat mengedit karya milik siswa lain.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $project['nama_project'] = trim($_POST['nama_project'] ?? '');
    $project['deskripsi']    = trim($_POST['deskripsi'] ?? '');
    $project['link_project'] = trim($_POST['link_project'] ?? '');
    $gambar_lama = $project['gambar'];
    $gambar_baru = null;

    if ($project['nama_project'] === '' || $project['deskripsi'] === '') {
        $error = "Nama karya dan deskripsi wajib diisi.";
    } elseif (!valid_link($project['link_project'])) {
        $error = "Link project harus diawali http:// atau https://";
    } else {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $gambar_baru = simpan_gambar($_FILES['gambar'], $error);
        }

        if ($error === '') {
            $stmt = $pdo->prepare("UPDATE projects
                                   SET nama_project = :nama, deskripsi = :desk, gambar = :img, link_project = :link
                                   WHERE id = :id");
            $stmt->execute([
                ':nama' => $project['nama_project'],
                ':desk' => $project['deskripsi'],
                ':img'  => $gambar_baru ?? $gambar_lama,
                ':link' => $project['link_project'],
                ':id'   => $id
            ]);

            if ($gambar_baru) {
                hapus_gambar($gambar_lama);
            }

            header('Location: index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-container">
    <h2>Edit Karya Digital</h2>
    <?php if ($error): ?>
        <div class="alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama_project">Nama Karya</label>
            <input type="text" id="nama_project" name="nama_project" value="<?= e($project['nama_project']) ?>" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" required><?= e($project['deskripsi']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="link_project">Link Project</label>
            <input type="url" id="link_project" name="link_project" value="<?= e($project['link_project']) ?>" required>
        </div>
        <div class="form-group">
            <label>Gambar Saat Ini</label>
            <img class="current-image" src="uploads/<?= e($project['gambar']) ?>" alt="<?= e($project['nama_project']) ?>">
        </div>
        <div class="form-group">
            <label for="gambar">Ganti Gambar (opsional, maks. 2 MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
