<?php
require_once 'config/kuis_db.php';
require_once 'config/upload.php';
require_once 'middleware/auth_check.php';

check_login();

$error = '';
$nilai = ['nama_project' => '', 'deskripsi' => '', 'link_project' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $nilai['nama_project'] = trim($_POST['nama_project'] ?? '');
    $nilai['deskripsi']    = trim($_POST['deskripsi'] ?? '');
    $nilai['link_project'] = trim($_POST['link_project'] ?? '');

    if ($nilai['nama_project'] === '' || $nilai['deskripsi'] === '') {
        $error = "Nama karya dan deskripsi wajib diisi.";
    } elseif (!valid_link($nilai['link_project'])) {
        $error = "Link project harus diawali http:// atau https://";
    } elseif (!isset($_FILES['gambar'])) {
        $error = "Gambar karya wajib diunggah.";
    } else {
        $gambar = simpan_gambar($_FILES['gambar'], $error);

        if ($gambar) {
            $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project)
                                   VALUES (:uid, :nama, :desk, :img, :link)");
            $stmt->execute([
                ':uid'  => $_SESSION['user_id'],
                ':nama' => $nilai['nama_project'],
                ':desk' => $nilai['deskripsi'],
                ':img'  => $gambar,
                ':link' => $nilai['link_project']
            ]);
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
    <title>Tambah Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-container">
    <h2>Tambah Karya Digital</h2>
    <?php if ($error): ?>
        <div class="alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama_project">Nama Karya</label>
            <input type="text" id="nama_project" name="nama_project" value="<?= e($nilai['nama_project']) ?>" required>
        </div>
        <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" required><?= e($nilai['deskripsi']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="link_project">Link Project</label>
            <input type="url" id="link_project" name="link_project" value="<?= e($nilai['link_project']) ?>" required>
        </div>
        <div class="form-group">
            <label for="gambar">Gambar Karya (maks. 2 MB)</label>
            <input type="file" id="gambar" name="gambar" accept="image/*" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Karya</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
