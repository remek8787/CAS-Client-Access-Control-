<?php
require('auth.php');
require('includes/_layout.php');
$logFile = 'log_aktivitas.txt';
$logLines = [];
if (file_exists($logFile)) {
    $logLines = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}
$GLOBALS['cas_current_page']='logs';
cas_page_start(['page'=>'logs','title'=>'Log Aktivitas','subtitle'=>'Riwayat aktivitas operator CAS untuk audit cepat.','kicker'=>'Audit Trail','icon'=>'📋','extraHead'=>'<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>']);
?>
<?php if (empty($logLines)): ?>
    <div class="cas-card cas-empty-state"><div class="cas-empty-icon">📋</div><strong>Belum ada log aktivitas</strong><span>Aktivitas operator akan tampil di sini setelah ada aksi pada CAS.</span></div>
<?php else: ?>
    <div class="cas-card cas-table-card"><div class="cas-table-head cas-grad-dark"><h5>📄 Log Aktivitas Pengguna</h5><span><?= count($logLines) ?> log</span></div><div class="cas-table-wrap"><table id="logTable" class="table table-hover table-striped align-middle"><thead><tr><th>Log</th></tr></thead><tbody><?php foreach ($logLines as $line): ?><tr><td><?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php endif; ?>
<?php cas_page_end('<script>$(function(){ $("#logTable").DataTable({pageLength:10,order:[],language:{search:"Cari Log:",lengthMenu:"Tampilkan _MENU_ log",zeroRecords:"Tidak ditemukan",info:"Menampilkan _START_ - _END_ dari _TOTAL_ log",infoEmpty:"Tidak ada log",paginate:{next:"→",previous:"←"}}});});</script>'); ?>
