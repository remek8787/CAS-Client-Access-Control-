<?php
require('auth.php');
require('bridge_client.php');
require('includes/_layout.php');
require('includes/_cas_data.php');

$routers = bridge_routers();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connect']) && isset($_POST['router'])) {
    $_SESSION['router'] = (int)$_POST['router'];
    header("Location: index.php");
    exit;
}
$routerId = cas_current_router_id($routers);
$data = cas_load_dashboard_data($routerId, $routers);
$activeRouter = $data['router'];
$GLOBALS['cas_current_page'] = 'dashboard';
cas_page_start([
    'page' => 'dashboard',
    'title' => 'Dashboard CAS',
    'subtitle' => 'Ringkasan cepat PPPoE. Tabel besar dipisah agar dashboard lebih ringan dan MikroTik tidak terbebani.',
    'kicker' => $activeRouter ? 'Router aktif: ' . ($activeRouter['name'] ?? 'Router') : 'Bridge Mode Aktif',
    'icon' => '📊'
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
    </form>
</div>

<?php if (empty($routers)): ?>
    <div class="cas-alert cas-alert-danger mb-3">❌ Bridge belum mengembalikan daftar router.</div>
<?php elseif ($activeRouter && !empty($data['error'])): ?>
    <div class="cas-alert cas-alert-danger mb-3">❌ Gagal dari bridge: <?= h($data['error']) ?></div>
<?php elseif ($activeRouter): ?>
    <div class="cas-alert cas-alert-success mb-3">✅ Terhubung via bridge ke <strong><?= h($activeRouter['name']) ?></strong><?= cas_cache_badge($data) ?></div>
<?php else: ?>
    <div class="cas-alert cas-alert-info mb-3">ℹ️ Pilih router lalu klik koneksikan untuk mulai membaca ringkasan PPPoE.</div>
<?php endif; ?>

<div class="cas-grid mb-3">
    <a class="cas-card cas-stat-link" href="ppp_users.php" style="grid-column:span 4" aria-label="Buka daftar PPPoE users"><div class="cas-stat"><div class="cas-stat-icon cas-grad-blue">📋</div><div><strong><?= h($data['total']) ?></strong><span>Daftar PPPoE Users</span><small>Buka halaman sendiri</small></div></div></a>
    <a class="cas-card cas-stat-link" href="active_users.php" style="grid-column:span 4" aria-label="Buka user aktif"><div class="cas-stat"><div class="cas-stat-icon cas-grad-green">⚡</div><div><strong><?= h($data['active_total']) ?></strong><span>User Aktif</span><small>Buka halaman sendiri</small></div></div></a>
    <a class="cas-card cas-stat-link cas-stat-link-danger" href="isolir_users.php" style="grid-column:span 4" aria-label="Buka user isolir"><div class="cas-stat"><div class="cas-stat-icon cas-grad-red">🔒</div><div><strong><?= h($data['isolir_total']) ?></strong><span>User ISOLIREBILLING</span><small>Cek isolir</small></div></div></a>
</div>

<div class="cas-card cas-card-lg">
    <h2 class="cas-card-title">Mode ringan aktif</h2>
    <p class="cas-card-muted mb-3">Dashboard sekarang hanya mengambil ringkasan dari cache bridge. Tabel besar dibuka saat dibutuhkan saja.</p>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-primary" href="ppp_users.php">📋 Daftar PPPoE</a>
        <a class="btn btn-success" href="active_users.php">⚡ User Aktif</a>
        <a class="btn btn-danger" href="isolir_users.php">🔒 User Isolir</a>
    </div>
</div>

<?php cas_page_end(); ?>
