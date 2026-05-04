<?php
require('auth.php');
require('bridge_client.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['router'], $_POST['.id'])) {
    $routerId = (int)$_POST['router'];
    $id = trim($_POST['.id']);
    bridge_post('/ppp/disconnect', [
        'router_id' => $routerId,
        'id' => $id
    ]);
}
header("Location: index.php");
exit;
?>
