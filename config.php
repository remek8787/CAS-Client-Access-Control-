<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$routers = file_exists('routers.json') ? json_decode(file_get_contents('routers.json'), true) : [];
?>
