<?php
require('auth.php');
require('bridge_client.php');
require('includes/_layout.php');

$routers = bridge_routers();
$pppSecrets = $pppActive = $pppProfiles = [];
$total = 0;
$pppActiveTotal = 0;
$connectError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connect']) && isset($_POST['router'])) {
    $_SESSION['router'] = (int)$_POST['router'];
    header("Location: index.php");
    exit;
}

$routerId = isset($_SESSION['router']) ? (int)$_SESSION['router'] : null;
$activeRouter = $routerId !== null && isset($routers[$routerId]) ? $routers[$routerId] : null;

if ($activeRouter) {
    $payload = ['router_id' => $routerId];
    $secretsRes = bridge_post('/ppp/secrets', $payload);
    $activeRes = bridge_post('/ppp/active', $payload);
    $profilesRes = bridge_post('/ppp/profiles', $payload);
    if (!empty($secretsRes['ok'])) { $pppSecrets = $secretsRes['data'] ?? []; $total = count($pppSecrets); } else { $connectError = $secretsRes['detail'] ?? $secretsRes['error'] ?? 'Gagal mengambil PPP Secret dari bridge.'; }
    if (!empty($activeRes['ok'])) { $pppActive = $activeRes['data'] ?? []; $pppActiveTotal = count($pppActive); }
    if (!empty($profilesRes['ok'])) { $pppProfiles = $profilesRes['data'] ?? []; }
}
$isolirCount = 0; foreach ($pppSecrets as $tmpUser) { if (($tmpUser['profile'] ?? '') === 'ISOLIREBILLING') { $isolirCount++; } }
$GLOBALS['cas_current_page'] = 'dashboard';
cas_page_start([
    'page' => 'dashboard',
    'title' => 'Dashboard CAS',
    'subtitle' => 'Pantau PPPoE, ubah profile, isolir, buka isolir, dan disconnect user aktif dari satu panel.',
    'kicker' => $activeRouter ? 'Router aktif: ' . ($activeRouter['name'] ?? 'Router') : 'Bridge Mode Aktif',
    'icon' => '📊',
    'extraHead' => '<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>'
]);
?>

<div class="cas-card cas-card-lg mb-3">
    <form method="POST" class="cas-router-panel">
        <select name="router" class="form-select" required>
            <option disabled <?= $routerId === null ? 'selected' : '' ?>>Pilih Router MikroTik</option>
            <?php foreach ($routers as $id => $router): ?>
                <option value="<?= h($id) ?>" <?= $routerId === (int)$id ? 'selected' : '' ?>><?= h($router['name'] ?? 'Router') ?> · <?= h($router['ip'] ?? '-') ?>:<?= h($router['port'] ?? '-') ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="connect" class="btn btn-primary"><span class="cas-submit-spinner"></span>🔌 Koneksikan</button>
        <a href="manage_routers.php" class="btn btn-outline-secondary">⚙️ Kelola Router</a>
        <?php if ($activeRouter): ?><a href="manage_vlan.php?router=<?= h($routerId) ?>" class="btn btn-outline-secondary">🌐 VLAN</a><?php endif; ?>
    </form>
</div>

<?php if (empty($routers)): ?>
    <div class="cas-alert cas-alert-danger mb-3">❌ Bridge belum mengembalikan daftar router.</div>
<?php elseif ($activeRouter && $connectError): ?>
    <div class="cas-alert cas-alert-danger mb-3">❌ Gagal dari bridge: <?= h($connectError) ?></div>
<?php elseif ($activeRouter): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ Terhubung via bridge ke <strong><?= h($activeRouter['name']) ?></strong></div>
<?php else: ?>
    <div class="cas-alert cas-alert-info mb-3">ℹ️ Pilih router lalu klik koneksikan untuk mulai membaca data PPPoE.</div>
<?php endif; ?>

<div class="cas-grid mb-3">
    <a class="cas-card cas-stat-link" href="#secrets-section" style="grid-column:span 4" aria-label="Lihat total user PPPoE"><div class="cas-stat"><div class="cas-stat-icon cas-grad-blue">👥</div><div><strong><?= h($total) ?></strong><span>Total User</span><small>Lihat daftar PPPoE</small></div></div></a>
    <a class="cas-card cas-stat-link" href="#active-section" style="grid-column:span 4" aria-label="Lihat user aktif"><div class="cas-stat"><div class="cas-stat-icon cas-grad-green">⚡</div><div><strong><?= h($pppActiveTotal) ?></strong><span>User Aktif</span><small>Lihat yang online</small></div></div></a>
    <a class="cas-card cas-stat-link cas-stat-link-danger" href="#isolir-section" style="grid-column:span 4" aria-label="Lihat user isolir"><div class="cas-stat"><div class="cas-stat-icon cas-grad-red">🔒</div><div><strong><?= h($isolirCount) ?></strong><span>User Isolir</span><small>Klik untuk cek isolir</small></div></div></a>
</div>

<?php if ($activeRouter && !$connectError): ?>
<div class="cas-card cas-table-card mb-4" id="secrets-section">
    <div class="cas-table-head cas-grad-blue"><h5>📋 Daftar PPPoE Users</h5><span><?= h($total) ?> secret</span></div>
    <div class="cas-table-wrap"><table id="secretsTable" class="table table-hover table-striped align-middle"><thead><tr><th>Username</th><th>Profile</th><th>Last Logout</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($pppSecrets as $user): ?><tr><td><strong><?= h($user['name'] ?? '-') ?></strong></td><td><?= h($user['profile'] ?? '-') ?></td><td><?= h($user['last-logged-out'] ?? '-') ?></td><td><form action="change_profile.php" method="POST" class="d-flex flex-wrap gap-2"><input type="hidden" name="name" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><select name="profile" class="form-select form-select-sm" style="max-width:240px"><?php foreach ($pppProfiles as $prof): ?><option value="<?= h($prof['name'] ?? '') ?>" <?= ($prof['name'] ?? '') === ($user['profile'] ?? '') ? 'selected' : '' ?>><?= h($prof['name'] ?? '') ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary"><span class="cas-submit-spinner"></span>Ubah</button></form><form action="isolir_user.php" method="POST" class="mt-2"><input type="hidden" name="username" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Isolir user ini?')"><span class="cas-submit-spinner"></span>🔒 Isolasi</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
</div>

<div class="cas-card cas-table-card mb-4" id="active-section">
    <div class="cas-table-head cas-grad-green"><h5>⚡ User Aktif</h5><span><?= h($pppActiveTotal) ?> online</span></div>
    <div class="cas-table-wrap"><table id="activeTable" class="table table-hover table-striped align-middle"><thead><tr><th>Username</th><th>IP</th><th>Uptime</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($pppActive as $u): ?><tr><td><strong><?= h($u['name'] ?? '-') ?></strong></td><td><?= h($u['address'] ?? '-') ?></td><td><?= h($u['uptime'] ?? '-') ?></td><td><form action="disconnect_user.php" method="POST"><input type="hidden" name=".id" value="<?= h($u['.id'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Putuskan koneksi user ini?')"><span class="cas-submit-spinner"></span>Putuskan</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
</div>

<div class="cas-card cas-table-card" id="isolir-section">
    <div class="cas-table-head cas-grad-red"><h5>🔒 User ISOLIREBILLING</h5><span><?= h($isolirCount) ?> isolir</span></div>
    <div class="cas-table-wrap"><table id="isolirTable" class="table table-hover table-striped align-middle"><thead><tr><th>Username</th><th>Profile</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($pppSecrets as $user): ?><?php if (($user['profile'] ?? '') === 'ISOLIREBILLING'): ?><tr><td><strong><?= h($user['name'] ?? '-') ?></strong></td><td><?= h($user['profile'] ?? '-') ?></td><td><form action="change_profile.php" method="POST" class="d-flex flex-wrap gap-2"><input type="hidden" name="name" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><select name="profile" class="form-select form-select-sm" style="max-width:240px"><?php foreach ($pppProfiles as $prof): ?><option value="<?= h($prof['name'] ?? '') ?>" <?= ($prof['name'] ?? '') === ($user['profile'] ?? '') ? 'selected' : '' ?>><?= h($prof['name'] ?? '') ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary"><span class="cas-submit-spinner"></span>Ubah</button></form></td></tr><?php endif; ?><?php endforeach; ?>
    </tbody></table></div>
</div>
<?php endif; ?>

<?php cas_page_end('<script>$(function(){ $("#secretsTable,#activeTable,#isolirTable").DataTable({pageLength:10,order:[],language:{search:"Cari:",lengthMenu:"Tampilkan _MENU_ data",zeroRecords:"Tidak ditemukan",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",paginate:{next:"→",previous:"←"}}});});</script>'); ?>
