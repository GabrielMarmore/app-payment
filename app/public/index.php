<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_ENTRY_POINT', true);

$page = $_GET['page'] ?? 'login';
if (isset($_SESSION['user_id']) && $page === 'login') {
    $page = 'dashboard';
}

// simple way to protect private pages
if (!in_array($page, ['login', 'register']) && empty($_SESSION['user_id'])) {
    header("Location: /index.php?page=login");
    exit();
}

//simple logout
if ($page === 'logout') {
    session_destroy();
    header("Location: /index.php?page=login");
    exit();
}

require_once __DIR__ . '/templates/header.php';
?>

<?php 
require_once __DIR__ . "/$page.php";
?>

<?php
require_once __DIR__ . '/templates/footer.php';
?>
