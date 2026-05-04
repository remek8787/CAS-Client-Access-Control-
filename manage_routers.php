<?php
require('auth.php');
require('bridge_client.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $payload = [
        'name' => trim($_POST['name'] ?? ''),
        'ip' => trim($_POST['ip'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'port' => (int)($_POST['port'] ?? 8728),
        'ssl' => isset($_POST['ssl'])
    ];

    if ($action === 'add') {
        $res = bridge_post('/routers/add', $payload);
        !empty($res['ok']) ? $message = 'Router berhasil ditambahkan ke bridge.' : $error = ($res['detail'] ?? $res['error'] ?? 'Gagal tambah router.');
    }

    if ($action === 'update') {
        $payload['router_id'] = (int)($_POST['router_id'] ?? -1);
        $res = bridge_post('/routers/update', $payload);
        !empty($res['ok']) ? $message = 'Router berhasil diperbarui.' : $error = ($res['detail'] ?? $res['error'] ?? 'Gagal update router.');
    }

    if ($action === 'delete') {
        $res = bridge_post('/routers/delete', ['router_id' => (int)($_POST['router_id'] ?? -1)]);
        !empty($res['ok']) ? $message = 'Router berhasil dihapus.' : $error = ($res['detail'] ?? $res['error'] ?? 'Gagal hapus router.');
    }
}

$routers = bridge_routers();
$testRouter = isset($_GET['test']) ? (int)$_GET['test'] : null;
$testResult = null;
if ($testRouter !== null) {
    $testResult = bridge_post('/test', ['router_id' => $testRouter]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAS Router Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets_cas.css?v=20260505-ux1" rel="stylesheet">
</head>
<body class="cas-body">
<div class="cas-shell">
    <div class="cas-topbar">
        <div class="cas-brand"><div class="cas-logo">🛡️</div><div><h1 class="cas-title">CAS Router Center</h1><p class="cas-subtitle">Client Access Control · Kelola koneksi API MikroTik via bridge Ubuntu</p></div></div>
        <div class="cas-actions"><a href="index.php" class="btn btn-light">Dashboard</a><a href="tutorial.php" class="btn btn-outline-light">Tutorial</a><a href="tambah_user.php" class="btn btn-outline-light">Pengguna</a><a href="logout.php" class="btn btn-danger">Logout</a></div>
    </div>

    <?php if ($message): ?><div class="cas-alert cas-alert-success mb-3">✅ <?= h($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="cas-alert cas-alert-danger mb-3">❌ <?= h($error) ?></div><?php endif; ?>
    <?php if ($testResult): ?>
        <div class="cas-alert <?= !empty($testResult['ok']) ? 'cas-alert-success' : 'cas-alert-danger' ?> mb-3">
            <?= !empty($testResult['ok']) ? '✅ Test router berhasil: ' . h($testResult['identity'][0]['name'] ?? 'identity terbaca') : '❌ Test gagal: ' . h($testResult['detail'] ?? $testResult['error'] ?? 'unknown') ?>
        </div>
    <?php endif; ?>

    <div class="cas-grid">
        <div class="cas-card" style="grid-column:span 5">
            <span class="cas-badge">➕ Tambah Router</span>
            <h4>Router API Baru</h4>
            <p class="text-muted mb-3">Port API bisa custom per router. Contoh: 8728, 8729, 29301, 29031, atau port NAT lain.</p>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <label class="fw-bold mb-1">Nama Router</label>
                <input class="form-control mb-3" name="name" placeholder="RO DSG FROM XL" required>
                <label class="fw-bold mb-1">IP / Host</label>
                <input class="form-control mb-3" name="ip" placeholder="192.0.2.1" required>
                <div class="row">
                    <div class="col-md-7"><label class="fw-bold mb-1">Username API</label><input class="form-control mb-3" name="username" placeholder="robot" required></div>
                    <div class="col-md-5"><label class="fw-bold mb-1">Port API</label><input class="form-control mb-3" type="number" min="1" max="65535" name="port" value="29301" required></div>
                </div>
                <label class="fw-bold mb-1">Password API</label>
                <input class="form-control mb-3" type="password" name="password" required>
                <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="ssl" id="sslAdd"><label class="form-check-label" for="sslAdd">Gunakan API SSL</label></div>
                <button class="btn btn-primary w-100">Simpan Router ke Bridge</button>
            </form>
        </div>

        <div class="cas-card cas-table-card" style="grid-column:span 7">
            <div class="cas-table-head bg-grad-dark"><h5 class="mb-0">📡 Router Terdaftar</h5><span><?= count($routers) ?> router</span></div>
            <div class="cas-table-wrap table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>ID</th><th>Router</th><th>IP:Port</th><th>User</th><th>SSL</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($routers as $id => $r): ?>
                        <tr>
                            <td><span class="cas-kbd"><?= h($id) ?></span></td>
                            <td><strong><?= h($r['name'] ?? '-') ?></strong></td>
                            <td><?= h($r['ip'] ?? '-') ?>:<strong><?= h($r['port'] ?? '-') ?></strong></td>
                            <td><?= h($r['username'] ?? '-') ?></td>
                            <td><?= !empty($r['ssl']) ? 'Ya' : 'Tidak' ?></td>
                            <td class="d-flex gap-2 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="manage_routers.php?test=<?= h($id) ?>">Test</a>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit<?= h($id) ?>">Edit</button>
                                <form method="POST" onsubmit="return confirm('Hapus router ini dari bridge?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="router_id" value="<?= h($id) ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit<?= h($id) ?>"><td colspan="6">
                            <form method="POST" class="row g-2 p-2 bg-light rounded-4">
                                <input type="hidden" name="action" value="update"><input type="hidden" name="router_id" value="<?= h($id) ?>">
                                <div class="col-md-3"><input class="form-control" name="name" value="<?= h($r['name'] ?? '') ?>" required></div>
                                <div class="col-md-3"><input class="form-control" name="ip" value="<?= h($r['ip'] ?? '') ?>" required></div>
                                <div class="col-md-2"><input class="form-control" name="username" value="<?= h($r['username'] ?? '') ?>" required></div>
                                <div class="col-md-2"><input class="form-control" type="number" name="port" min="1" max="65535" value="<?= h($r['port'] ?? 8728) ?>" required></div>
                                <div class="col-md-2"><input class="form-control" type="password" name="password" placeholder="Password API" required></div>
                                <div class="col-md-3 form-check form-switch ms-2"><input class="form-check-input" type="checkbox" name="ssl" id="ssl<?= h($id) ?>" <?= !empty($r['ssl']) ? 'checked' : '' ?>><label class="form-check-label" for="ssl<?= h($id) ?>">SSL</label></div>
                                <div class="col-md-3"><button class="btn btn-success btn-sm">Simpan Edit</button></div>
                            </form>
                        </td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
