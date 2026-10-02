<?php
session_start();
// Temporary demo password-only login until real backend authentication is connected.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../index.php?start=adminLogin'); exit; }
$password = (string)($_POST['password'] ?? '');
if (hash_equals('WAdmin', $password)) {
    session_regenerate_id(true);
    $_SESSION['is_admin'] = true;
    $_SESSION['active_role'] = 'admin';
    header('Location: ../index.php?start=grades'); exit;
}
header('Location: ../index.php?start=adminLogin&error=1'); exit;
