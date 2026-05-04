<?php
require('config.php');

$new = [
    'name' => $_POST['name'],
    'ip' => $_POST['ip'],
    'username' => $_POST['username'],
    'password' => $_POST['password'],
    'port' => (int)$_POST['port']
];

$routers[] = $new;
file_put_contents('routers.json', json_encode($routers, JSON_PRETTY_PRINT));
header("Location: manage_routers.php");
