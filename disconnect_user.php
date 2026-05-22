<?php
require('auth.php');
require('bridge_client.php');

function cas_safe_return($fallback = 'index.php') {
    $return = isset($_POST['return_to']) ? $_POST['return_to'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $fallback);
    $parts = parse_url($return);
    $path = basename(isset($parts['path']) ? $parts['path'] : $fallback);
    $allowed = array('index.php','ppp_users.php','active_users.php','isolir_users.php','manage_vlan.php','manage_routers.php','tambah_user.php','log_aktivitas.php','tutorial.php');
    if (!in_array($path, $allowed, true)) { $path = $fallback; }
    $query = array();
    if (!empty($parts['query'])) { parse_str($parts['query'], $query); }
    return array($path, $query);
}
function cas_redirect_back($statusKey, $message, $fallback = 'index.php') {
    list($path, $query) = cas_safe_return($fallback);
    $query[$statusKey] = 'success';
    $query['msg'] = $message;
    header('Location: ' . $path . '?' . http_build_query($query));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['router'], $_POST['.id'])) {
    $routerId = (int)$_POST['router'];
    $id = trim($_POST['.id']);
    bridge_post('/ppp/disconnect', array(
        'router_id' => $routerId,
        'id' => $id
    ));
    cas_redirect_back('disconnect', 'Berhasil disconnect user aktif', 'active_users.php');
}
header("Location: index.php");
exit;
?>
