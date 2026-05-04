<?php
require('auth.php');
$logFile = 'log_aktivitas.txt';
$logLines = [];

if (file_exists($logFile)) {
    $logLines = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
</head>
<body class="p-4 bg-light">
<div class="container">
    <h4 class="mb-4">📄 Log Aktivitas Pengguna</h4>

    <?php if (empty($logLines)): ?>
        <div class="alert alert-warning">Belum ada log aktivitas.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table id="logTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>Log</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($logLines as $line): ?>
                        <tr><td><?= htmlspecialchars($line) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-3">⬅️ Kembali ke Dashboard</a>
</div>

<script>
$(document).ready(function () {
    $('#logTable').DataTable({
        pageLength: 10,
        order: [],
        language: {
            search: "🔍 Cari Log:",
            lengthMenu: "Tampilkan _MENU_ log",
            zeroRecords: "❌ Tidak ditemukan",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            infoEmpty: "Tidak ada log",
            paginate: {
                next: "➡️",
                previous: "⬅️"
            }
        }
    });
});
</script>
</body>
</html>
