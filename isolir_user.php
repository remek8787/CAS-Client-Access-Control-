<?php
require('auth.php');
require('bridge_client.php');
require('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['router'])) {
    $username = trim($_POST['username']);
    $routerId = (int)$_POST['router'];

    $res = bridge_post('/ppp/isolate', [
        'router_id' => $routerId,
        'username' => $username,
        'profile' => 'ISOLIREBILLING'
    ]);

    if (!empty($res['ok'])) {
        logAktivitas("mengisolir user '$username' via bridge", $_SESSION['username']);
        header("Location: index.php?isolir=success");
        exit;
    }

    die("❌ Gagal isolir via bridge: " . h($res['error'] ?? 'unknown'));
}
header("Location: index.php");
exit;
?>
