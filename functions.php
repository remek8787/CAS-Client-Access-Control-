<?php
function logAktivitas($aksi, $oleh) {
    $waktu = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $log = "[$waktu] [$ip] [$oleh] $aksi" . PHP_EOL;
    file_put_contents('log_aktivitas.txt', $log, FILE_APPEND);
}
?>
