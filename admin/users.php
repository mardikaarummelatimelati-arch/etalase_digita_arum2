<?php
require_once '../config/kuis_db.php';
require_once '../middleware/auth_check.php';

check_role(['super_admin']);

$error = '';
$info  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['tambah_admin'])) {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            $error = "Isi nama, email valid, dan password minimal 6 karakter.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, 'admin')");
                $stmt->execute([
                    ':nama'  => $nama,
                    ':email' => $email,
                    ':pass'  => password_hash($pass, PASSWORD_BCRYPT)
                ]);
                $info = "Admin baru berhasil ditambahkan.";
            } catch (PDOException $e) {
                $error = "Email sudah terdaftar.";
            }
        }
    }

    if (isset($_POST['hapus_user'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'super_admin'");
        $stmt->execute([':id' => (int) $_POST['hapus_user']]);
        header('Location: users.php');
        exit();
    }
}

$konfirmasi = null;
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE id = :id AND role != 'super_admin'");
    $stmt->execute([':id' => (int) $_GET['hapus']]);
    $konfirmasi = $stmt->fetch();
}

$users = $pdo->query("SELECT * FROM users ORDER BY FIELD(role, 'super_admin', 'admin', 'siswa'), nama ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<nav class="navbar">
    <h2>Manajemen User</h2>
    <div class="navbar-user">
        <a href="index.php" class="btn btn-secondary">Dashboard</a>
        <a href="../index.php" class="btn btn-secondary">Etalase</a>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<main class="admin-container">
    <?php if ($error): ?><div class="alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="alert-success"><?= e($info) ?></div><?php endif; ?>

    <?php if ($konfirmasi): ?>
        <div class="table-container">
            <p class="confirm-text">Hapus user <b><?= e($konfirmasi['nama']) ?></b>? Semua karya milik user ini ikut terhapus.</p>
            <form method="POST" class="form-actions">
                <?= csrf_field() ?>
                <input type="hidden" name="hapus_user" value="<?= (int) $konfirmasi['id'] ?>">
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                <a href="users.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <h3>Tambah Admin Baru</h3>
        <form method="POST" class="inline-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <button type="submit" name="tambah_admin" class="btn btn-primary">Tambah Admin</button>
        </form>
    </div>

    <div class="table-container">
        <h3>Daftar User</h3>
        <table class="custom-table">
            <tr>
                <th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= e($u['nama']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge-role badge-<?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
                <td>
                    <?php if ($u['role'] !== 'super_admin'): ?>
                        <a href="users.php?hapus=<?= (int) $u['id'] ?>" class="btn btn-danger">Hapus</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</body>
</html>
