<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$users = json_decode(file_get_contents('users.json'), true);
$error = '';
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUser = $_POST['username'] ?? '';
    $inputPass = $_POST['password'] ?? '';
    foreach ($users as $user) {
        if (($user['username'] ?? '') === $inputUser && ($user['password'] ?? '') === $inputPass) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            header("Location: index.php");
            exit;
        }
    }
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login CAS - Client Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="cas-theme.css?v=20260523-premium1" rel="stylesheet">
</head>
<body>
<div class="cas-login-shell">
    <div class="cas-login-card">
        <div class="cas-login-mark">CAS</div>
        <div class="text-center mb-4">
            <h1 class="cas-page-title mb-1">Masuk CAS</h1>
            <p class="text-muted mb-0"><strong>Client Access Control</strong><br>PPPoE & MikroTik Bridge Console</p>
        </div>
        <?php if ($timeout): ?>
            <div class="cas-alert cas-alert-warning mb-3">⏱️ Sesi habis karena tidak aktif. Silakan login ulang.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="cas-alert cas-alert-danger mb-3"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST">
            <label class="fw-bold mb-1">Username</label>
            <input type="text" name="username" class="form-control mb-3" placeholder="Masukkan username" required autofocus autocomplete="username">
            <label class="fw-bold mb-1">Password</label>
            <input type="password" name="password" class="form-control mb-4" placeholder="Masukkan password" required autocomplete="current-password">
            <button class="btn btn-primary w-100 py-2"><span class="cas-submit-spinner"></span>Masuk ke Control Center</button>
        </form>
        <div class="cas-login-trust">
            <span>🛡️ <span><b>Bridge Mode</b> — panel tidak mengubah arsitektur koneksi yang sudah berjalan.</span></span>
            <span>⚡ <span><b>Cepat untuk operator</b> — masuk, pilih router, eksekusi aksi penting.</span></span>
            <span>📱 <span><b>Mobile ready</b> — tampilan tetap nyaman saat dipakai dari HP.</span></span>
        </div>
        <div class="text-center mt-4 text-muted small">CAS v1.2 Premium UI · Bridge Mode</div>
    </div>
</div>
<script src="includes/_loader.js?v=20260523-premium1"></script>
</body>
</html>
