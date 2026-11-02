<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_proyek = str_replace('\\', '/', dirname(__DIR__));
$root_web    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
define('BASE_URL', rtrim(str_replace($root_web, '', $root_proyek), '/'));

function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit();
    }
}

function check_role(array $roles)
{
    check_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die("Akses Ditolak: Anda tidak memiliki hak akses ke halaman ini.");
    }
}

function can_manage(array $project)
{
    return in_array($_SESSION['role'], ['super_admin', 'admin'], true)
        || (int) $_SESSION['user_id'] === (int) $project['user_id'];
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_verify()
{
    $kirim = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $kirim)) {
        http_response_code(400);
        die("Permintaan tidak valid. Muat ulang halaman lalu coba lagi.");
    }
}

function valid_link($url)
{
    return filter_var($url, FILTER_VALIDATE_URL)
        && in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}
