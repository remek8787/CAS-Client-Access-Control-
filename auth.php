<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Auto logout setelah 5 menit tidak aktif
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 300) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();
