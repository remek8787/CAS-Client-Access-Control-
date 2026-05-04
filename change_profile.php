<?php
require('auth.php');
require('bridge_client.php');
require('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['profile'], $_POST['router'])) {
    $username = trim($_POST['name']);
    $profile = trim($_POST['profile']);
    $routerId = (int)$_POST['router'];

    $res = bridge_post('/ppp/change-profile', [
        'router_id' => $routerId,
        'username' => $username,
        'profile' => $profile
    ]);

    if (!empty($res['ok'])) {
        logAktivitas("mengubah profile user '$username' menjadi '$profile' via bridge", $_SESSION['username']);
        header("Location: index.php?ubah=success");
        exit;
    }

    die("❌ Gagal ubah profile via bridge: " . h($res['error'] ?? 'unknown'));
}
header("Location: index.php");
exit;
?>
