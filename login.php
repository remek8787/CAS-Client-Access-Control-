<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$users = json_decode(file_get_contents('users.json'), true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUser = $_POST['username'];
    $inputPass = $_POST['password'];
    foreach ($users as $user) {
        if ($user['username'] === $inputUser && $user['password'] === $inputPass) {
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
    <title>CAS Login - Client Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets_cas.css?v=20260505-ux1" rel="stylesheet">
</head>
<body class="cas-body">
<div class="login-shell">
    <div class="login-card">
        <div class="login-mark">🛡️</div>
        <h3>CAS</h3>
        <p class="mb-4"><strong>Client Access Control</strong><br>PPPoE & MikroTik Bridge Console</p>
        <?php if ($error): ?>
            <div class="cas-alert cas-alert-danger mb-3"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="POST">
            <label class="fw-bold mb-1">Username</label>
            <input type="text" name="username" class="form-control mb-3" placeholder="Masukkan username" required autofocus>
            <label class="fw-bold mb-1">Password</label>
            <input type="password" name="password" class="form-control mb-4" placeholder="Masukkan password" required>
            <button class="btn btn-primary w-100 py-2">Masuk ke Control Center</button>
        </form>
        <div class="text-center mt-4 text-muted small">CAS v1.1 Bridge Mode · DSG Network</div>
    </div>
</div>
</body>
</html>
