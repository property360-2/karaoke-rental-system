<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /karaoke-rental/user/login.php');
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header('Location: /karaoke-rental/admin/login.php');
        exit();
    }
}
?> 