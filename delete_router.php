<?php
require('config.php');

$index = (int)($_GET['index'] ?? -1);
if ($index >= 0 && $index < count($routers)) {
    array_splice($routers, $index, 1);
    file_put_contents('routers.json', json_encode($routers, JSON_PRETTY_PRINT));
}

header("Location: manage_routers.php");
