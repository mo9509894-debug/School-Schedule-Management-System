<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Selecting Student switches out of admin mode but keeps a clean Student role.
if (($_GET['as'] ?? '') === 'student') {
    unset($_SESSION['is_admin']);
    $_SESSION['active_role'] = 'student';
    session_regenerate_id(true);
    header('Location: ../index.php?start=grades');
    exit;
}
// A real logout clears the role and destroys the session.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: ../index.php');
exit;
