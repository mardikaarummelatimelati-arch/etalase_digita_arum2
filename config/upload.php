<?php
function simpan_gambar(array $file, &$error)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Gambar gagal diunggah.";
        return null;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "Ukuran gambar maksimal 2 MB.";
        return null;
    }

    $diizinkan = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp'
    ];

    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($diizinkan[$info[2]])) {
        $error = "File harus berupa gambar JPG, PNG, GIF, atau WEBP.";
        return null;
    }

    $nama = bin2hex(random_bytes(12)) . '.' . $diizinkan[$info[2]];
    $tujuan = dirname(__DIR__) . '/uploads/' . $nama;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        $error = "Gambar tidak dapat disimpan ke folder uploads.";
        return null;
    }

    return $nama;
}

function hapus_gambar($nama)
{
    $path = dirname(__DIR__) . '/uploads/' . basename($nama);
    if (is_file($path)) {
        unlink($path);
    }
}
