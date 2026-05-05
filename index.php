<?php
require('auth.php');
require('bridge_client.php');

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAS - Client Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets_cas.css?v=20260505-ux1" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
</head>
<body class="cas-body">
<div class="cas-shell">
    <div class="cas-topbar">
        <div class="cas-brand"><div class="cas-logo">🛡️</div><div><h1 class="cas-title">CAS</h1><p class="cas-subtitle">Client Access Control · PPPoE & MikroTik Bridge Console</p></div></div>
        <div class="cas-actions"><a href="manage_routers.php" class="btn btn-light">Router</a><a href="tutorial.php" class="btn btn-outline-light">Tutorial</a><a href="tambah_user.php" class="btn btn-outline-light">Pengguna</a><?php if ($activeRouter): ?><a href="manage_vlan.php?router=<?= h($routerId) ?>" class="btn btn-outline-light">VLAN</a><?php endif; ?><span class="cas-user-pill">👤 <?= h($_SESSION['username']) ?></span><a href="logout.php" class="btn btn-danger">Logout</a></div>
    </div>

    <div class="cas-hero">
        <span class="cas-badge">Bridge Mode Aktif</span>
        <h2>Client Access Control</h2>
        <p>Kelola akses pelanggan PPPoE dari satu panel: pantau user aktif, ubah profile, isolir, buka isolir, disconnect user, dan kontrol VLAN/router dengan cepat.</p>
    </div>

    <div class="cas-card mb-3">
        <form method="POST" class="cas-router-panel">
            <select name="router" class="form-select" required>
                <option disabled <?= $routerId === null ? 'selected' : '' ?>>Pilih Router MikroTik</option>
                <?php foreach ($routers as $id => $router): ?><option value="<?= h($id) ?>" <?= $routerId === (int)$id ? 'selected' : '' ?>><?= h($router['name'] ?? 'Router') ?> · <?= h($router['ip'] ?? '-') ?>:<?= h($router['port'] ?? '-') ?></option><?php endforeach; ?>
            </select>
            <button type="submit" name="connect" class="btn btn-primary">🔌 Koneksikan</button>
            <a href="manage_routers.php" class="btn btn-outline-secondary">⚙️ Kelola Router</a>
        </form>
    </div>

    <?php if (empty($routers)): ?><div class="cas-alert cas-alert-danger mb-3">❌ Bridge belum mengembalikan daftar router.</div><?php elseif ($activeRouter && $connectError): ?><div class="cas-alert cas-alert-danger mb-3">❌ Gagal dari bridge: <?= h($connectError) ?></div><?php elseif ($activeRouter): ?><div class="cas-alert cas-alert-success mb-3">✅ Terhubung via bridge ke <strong><?= h($activeRouter['name']) ?></strong></div><?php else: ?><div class="cas-alert cas-alert-info mb-3">ℹ️ Pilih router lalu klik koneksikan untuk mulai membaca data PPPoE.</div><?php endif; ?>

    <div class="cas-grid mb-3">
        <div class="cas-card" style="grid-column:span 4"><div class="cas-stat"><div class="cas-stat-icon bg-grad-blue">👥</div><div><strong><?= h($total) ?></strong><span>Total User</span></div></div></div>
        <div class="cas-card" style="grid-column:span 4"><div class="cas-stat"><div class="cas-stat-icon bg-grad-green">⚡</div><div><strong><?= h($pppActiveTotal) ?></strong><span>User Aktif</span></div></div></div>
        <div class="cas-card" style="grid-column:span 4"><div class="cas-stat"><div class="cas-stat-icon bg-grad-red">🔒</div><div><strong><?php $isolirCount = 0; foreach ($pppSecrets as $tmpUser) { if (($tmpUser['profile'] ?? '') === 'ISOLIREBILLING') { $isolirCount++; } } echo h($isolirCount); ?></strong><span>User Isolir</span></div></div></div>
    </div>

    <?php if ($activeRouter && !$connectError): ?>
    <div class="cas-card cas-table-card mb-4"><div class="cas-table-head bg-grad-blue"><h5 class="mb-0">📋 Daftar PPPoE Users</h5><span><?= h($total) ?> secret</span></div><div class="cas-table-wrap table-responsive"><table id="secretsTable" class="table table-hover table-striped text-center"><thead><tr><th>Username</th><th>Profile</th><th>Last Logout</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pppSecrets as $user): ?><tr><td><strong><?= h($user['name'] ?? '-') ?></strong></td><td><?= h($user['profile'] ?? '-') ?></td><td><?= h($user['last-logged-out'] ?? '-') ?></td><td><form action="change_profile.php" method="POST" class="d-flex justify-content-center flex-wrap gap-2"><input type="hidden" name="name" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><select name="profile" class="form-select form-select-sm" style="max-width:240px"><?php foreach ($pppProfiles as $prof): ?><option value="<?= h($prof['name'] ?? '') ?>" <?= ($prof['name'] ?? '') === ($user['profile'] ?? '') ? 'selected' : '' ?>><?= h($prof['name'] ?? '') ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary">Ubah</button></form><form action="isolir_user.php" method="POST" class="mt-2"><input type="hidden" name="username" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Isolir user ini?')">🔒 Isolasi</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>

    <div class="cas-card cas-table-card mb-4"><div class="cas-table-head bg-grad-green"><h5 class="mb-0">⚡ User Aktif</h5><span><?= h($pppActiveTotal) ?> online</span></div><div class="cas-table-wrap table-responsive"><table id="activeTable" class="table table-hover table-striped text-center"><thead><tr><th>Username</th><th>IP</th><th>Uptime</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pppActive as $u): ?><tr><td><strong><?= h($u['name'] ?? '-') ?></strong></td><td><?= h($u['address'] ?? '-') ?></td><td><?= h($u['uptime'] ?? '-') ?></td><td><form action="disconnect_user.php" method="POST"><input type="hidden" name=".id" value="<?= h($u['.id'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><button class="btn btn-sm btn-danger" onclick="return confirm('Putuskan koneksi user ini?')">Putuskan</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>

    <div class="cas-card cas-table-card"><div class="cas-table-head bg-grad-red"><h5 class="mb-0">🔒 User ISOLIREBILLING</h5><span>isolir</span></div><div class="cas-table-wrap table-responsive"><table id="isolirTable" class="table table-hover table-striped text-center"><thead><tr><th>Username</th><th>Profile</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pppSecrets as $user): ?><?php if (($user['profile'] ?? '') === 'ISOLIREBILLING'): ?><tr><td><strong><?= h($user['name'] ?? '-') ?></strong></td><td><?= h($user['profile'] ?? '-') ?></td><td><form action="change_profile.php" method="POST" class="d-flex justify-content-center flex-wrap gap-2"><input type="hidden" name="name" value="<?= h($user['name'] ?? '') ?>"><input type="hidden" name="router" value="<?= h($routerId) ?>"><select name="profile" class="form-select form-select-sm" style="max-width:240px"><?php foreach ($pppProfiles as $prof): ?><option value="<?= h($prof['name'] ?? '') ?>" <?= ($prof['name'] ?? '') === ($user['profile'] ?? '') ? 'selected' : '' ?>><?= h($prof['name'] ?? '') ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary">Ubah</button></form></td></tr><?php endif; ?><?php endforeach; ?></tbody></table></div></div>
    <?php endif; ?>
    <p class="cas-footer-note">CAS v1.1 · Client Access Control · Bridge Mode</p>
</div>
<script>$(document).ready(function(){ $('#secretsTable,#activeTable,#isolirTable').DataTable({pageLength:10,order:[],language:{search:"Cari:",lengthMenu:"Tampilkan _MENU_ data",zeroRecords:"Tidak ditemukan",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",paginate:{next:"→",previous:"←"}}});});</script>
</body>
</html>
