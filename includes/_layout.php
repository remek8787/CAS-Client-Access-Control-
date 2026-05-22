<?php
if (!function_exists('cas_nav_items')) {
    function cas_nav_items() {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'index.php', 'icon' => '📊'],
            ['key' => 'pppoe', 'label' => 'PPPoE', 'href' => 'ppp_users.php', 'icon' => '📋'],
            ['key' => 'active', 'label' => 'Aktif', 'href' => 'active_users.php', 'icon' => '⚡'],
            ['key' => 'isolir', 'label' => 'Isolir', 'href' => 'isolir_users.php', 'icon' => '🔒'],
            ['key' => 'router', 'label' => 'Router', 'href' => 'manage_routers.php', 'icon' => '📡'],
            ['key' => 'vlan', 'label' => 'VLAN', 'href' => 'manage_vlan.php', 'icon' => '🌐'],
            ['key' => 'users', 'label' => 'Pengguna', 'href' => 'tambah_user.php', 'icon' => '👥'],
            ['key' => 'logs', 'label' => 'Log', 'href' => 'log_aktivitas.php', 'icon' => '📋'],
            ['key' => 'tutorial', 'label' => 'Tutorial', 'href' => 'tutorial.php', 'icon' => '📘'],
        ];
    }
}
if (!function_exists('cas_h')) {
    function cas_h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('cas_active')) {
    function cas_active($page, $key) { return $page === $key ? 'active' : ''; }
}
if (!function_exists('cas_render_sidebar')) {
    function cas_render_sidebar($page) {
        ?>
        <aside class="cas-sidebar">
            <div class="cas-side-brand">
                <div class="cas-logo">CAS</div>
                <div><div class="cas-brand-title">CAS</div><div class="cas-brand-subtitle">Client Access Control</div></div>
            </div>
            <nav class="cas-nav">
                <?php foreach (cas_nav_items() as $item): ?>
                    <a class="cas-nav-link <?= cas_active($page, $item['key']) ?>" href="<?= cas_h($item['href']) ?>">
                        <span class="cas-nav-icon"><?= cas_h($item['icon']) ?></span><span><?= cas_h($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="cas-sidebar-footer">CAS v1.2 Modern UI<br>Bridge Mode · DSG Network</div>
        </aside>
        <?php
    }
}
if (!function_exists('cas_render_bottom_nav')) {
    function cas_render_bottom_nav($page) {
        ?>
        <nav class="cas-bottom-nav">
            <?php foreach (cas_nav_items() as $item): ?>
                <a class="<?= cas_active($page, $item['key']) ?>" href="<?= cas_h($item['href']) ?>"><span><?= cas_h($item['icon']) ?></span><span><?= cas_h($item['label']) ?></span></a>
            <?php endforeach; ?>
        </nav>
        <?php
    }
}
if (!function_exists('cas_page_start')) {
    function cas_page_start($opts = []) {
        $title = $opts['title'] ?? 'CAS';
        $subtitle = $opts['subtitle'] ?? 'Client Access Control';
        $kicker = $opts['kicker'] ?? 'Bridge Mode';
        $page = $opts['page'] ?? '';
        $icon = $opts['icon'] ?? '🛡️';
        $extraHead = $opts['extraHead'] ?? '';
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= cas_h($title) ?> - CAS</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="cas-theme.css?v=20260522-modern1" rel="stylesheet">
    <?= $extraHead ?>
</head>
<body class="cas-body">
<div class="cas-app">
    <?php cas_render_sidebar($page); ?>
    <main class="cas-main">
        <div class="cas-mobile-top"><div class="d-flex align-items-center gap-2"><div class="cas-logo" style="width:42px;height:42px;border-radius:14px;font-size:15px">CAS</div><div><strong><?= cas_h($title) ?></strong><div class="small text-white-50">Client Access Control</div></div></div><a href="logout.php" class="btn btn-sm btn-danger">Logout</a></div>
        <div class="cas-topbar">
            <div>
                <span class="cas-page-kicker"><?= cas_h($icon) ?> <?= cas_h($kicker) ?></span>
                <h1 class="cas-page-title"><?= cas_h($title) ?></h1>
                <p class="cas-page-subtitle"><?= cas_h($subtitle) ?></p>
            </div>
            <div class="cas-user-actions">
                <span class="cas-user-pill">👤 <?= cas_h($_SESSION['username'] ?? 'User') ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        <?php
    }
}
if (!function_exists('cas_page_end')) {
    function cas_page_end($extraScripts = '') {
        ?>
        <p class="cas-footer-note">CAS v1.2 · Client Access Control · Bridge Mode</p>
    </main>
    <?php cas_render_bottom_nav($GLOBALS['cas_current_page'] ?? ''); ?>
</div>
<div class="cas-toast-zone" id="casToastZone"></div>
<script src="includes/_toast.js?v=20260522-modern1"></script>
<script src="includes/_loader.js?v=20260522-modern1"></script>
<?= $extraScripts ?>
</body>
</html>
        <?php
    }
}
?>
