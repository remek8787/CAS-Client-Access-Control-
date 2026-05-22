<?php
require('auth.php');
require('bridge_client.php');
require('includes/_layout.php');
require('includes/_cas_data.php');
$routers = bridge_routers();
$routerId = cas_current_router_id($routers);
$data = cas_load_dashboard_data($routerId, $routers);
$activeRouter = $data['router'];
$GLOBALS['cas_current_page'] = 'active';
cas_page_start([
    'page' => 'active',
    'title' => 'User Aktif',
    'subtitle' => 'Daftar PPP Active dalam halaman sendiri. Cocok untuk monitoring cepat dan disconnect user online.',
    'kicker' => $activeRouter ? 'Router: ' . ($activeRouter['name'] ?? 'Router') : 'Pilih Router',
    'icon' => '⚡',
    'extraHead' => '<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>'
]);
?>

<?php if (!empty($_GET['msg'])): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ <?= h($_GET['msg']) ?></div>
<?php elseif (isset($_GET['ubah'])): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ Berhasil merubah profile.</div>
<?php elseif (isset($_GET['isolir'])): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ Berhasil mengisolir user.</div>
<?php elseif (isset($_GET['disconnect'])): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ Berhasil disconnect user aktif.</div>
<?php endif; ?>

<div class="cas-card mb-3"><div class="cas-router-panel"><a href="index.php" class="btn btn-outline-secondary">← Dashboard</a><a href="ppp_users.php" class="btn btn-outline-primary">📋 PPPoE Users</a><a href="isolir_users.php" class="btn btn-outline-danger">🔒 User Isolir</a><?= cas_cache_badge($data) ?></div></div>
<?php if (!$activeRouter): ?><div class="cas-alert cas-alert-info">Pilih router dari dashboard dulu.</div><?php elseif (!empty($data['error'])): ?><div class="cas-alert cas-alert-danger">❌ <?= h($data['error']) ?></div><?php else: ?>
<div class="cas-card cas-table-card"><div class="cas-table-head cas-grad-green"><h5>⚡ User Aktif</h5><span><?= h($data['active_total']) ?> online</span></div><div class="cas-table-wrap"><table id="activeTable" class="table table-hover table-striped align-middle"><thead><tr><th>Username</th><th>IP</th><th>Uptime</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($data['active'] as $u): ?><tr><td><strong><?= h($u['name'] ?? '-') ?></strong></td><td><?= h($u['address'] ?? '-') ?></td><td><?= h($u['uptime'] ?? '-') ?></td><td><form action="disconnect_user.php" method="POST"><input type="hidden" name=".id" value="<?= h($u['.id'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><input type="hidden" name="return_to" value="<?= h(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : basename(__FILE__)) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Putuskan koneksi user ini?')"><span class="cas-submit-spinner"></span>Putuskan</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php endif; ?>
<?php cas_page_end('<script>$(function(){ $("#activeTable").DataTable({pageLength:25,order:[],language:{search:"Cari:",lengthMenu:"Tampilkan _MENU_ data",zeroRecords:"Tidak ditemukan",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",paginate:{next:"→",previous:"←"}}});});</script>'); ?>
