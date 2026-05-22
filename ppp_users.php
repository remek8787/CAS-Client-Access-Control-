<?php
require('auth.php');
require('bridge_client.php');
require('includes/_layout.php');
require('includes/_cas_data.php');
$routers = bridge_routers();
$routerId = cas_current_router_id($routers);
$data = cas_load_dashboard_data($routerId, $routers);
$activeRouter = $data['router'];
$GLOBALS['cas_current_page'] = 'pppoe';
cas_page_start([
    'page' => 'pppoe',
    'title' => 'Daftar PPPoE Users',
    'subtitle' => 'Semua PPP Secret dalam halaman sendiri. Data memakai cache bridge agar query ke MikroTik lebih hemat.',
    'kicker' => $activeRouter ? 'Router: ' . ($activeRouter['name'] ?? 'Router') : 'Pilih Router',
    'icon' => '📋',
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

<div class="cas-card mb-3"><div class="cas-router-panel"><a href="index.php" class="btn btn-outline-secondary">← Dashboard</a><a href="active_users.php" class="btn btn-outline-success">⚡ User Aktif</a><a href="isolir_users.php" class="btn btn-outline-danger">🔒 User Isolir</a><?= cas_cache_badge($data) ?></div></div>
<?php if (!$activeRouter): ?><div class="cas-alert cas-alert-info">Pilih router dari dashboard dulu.</div><?php elseif (!empty($data['error'])): ?><div class="cas-alert cas-alert-danger">❌ <?= h($data['error']) ?></div><?php else: ?>
<div class="cas-card cas-table-card"><div class="cas-table-head cas-grad-blue"><h5>📋 Daftar PPPoE Users</h5><span><?= h($data['total']) ?> secret</span></div><div class="cas-table-wrap"><table id="secretsTable" class="table table-hover table-striped align-middle"><thead><tr><th>Username</th><th>Profile</th><th>Last Logout</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($data['secrets'] as $user): ?><tr><td><strong><?= h($user['name'] ?? '-') ?></strong></td><td><?= h($user['profile'] ?? '-') ?></td><td><?= h($user['last-logged-out'] ?? '-') ?></td><td><form action="change_profile.php" method="POST" class="d-flex flex-wrap gap-2"><input type="hidden" name="name" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><input type="hidden" name="return_to" value="<?= h(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : basename(__FILE__)) ?>"><select name="profile" class="form-select form-select-sm" style="max-width:240px"><?php foreach ($data['profiles'] as $prof): ?><option value="<?= h($prof['name'] ?? '') ?>" <?= ($prof['name'] ?? '') === ($user['profile'] ?? '') ? 'selected' : '' ?>><?= h($prof['name'] ?? '') ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary"><span class="cas-submit-spinner"></span>Ubah</button></form><form action="isolir_user.php" method="POST" class="mt-2"><input type="hidden" name="username" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><input type="hidden" name="return_to" value="<?= h(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : basename(__FILE__)) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Isolir user ini?')"><span class="cas-submit-spinner"></span>🔒 Isolasi</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php endif; ?>
<?php cas_page_end('<script>$(function(){ $("#secretsTable").DataTable({pageLength:25,order:[],language:{search:"Cari:",lengthMenu:"Tampilkan _MENU_ data",zeroRecords:"Tidak ditemukan",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",paginate:{next:"→",previous:"←"}}});});</script>'); ?>
