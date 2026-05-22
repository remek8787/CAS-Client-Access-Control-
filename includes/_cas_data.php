<?php
if (!function_exists('cas_current_router_id')) {
    function cas_current_router_id($routers) {
        $routerId = isset($_GET['router']) ? (int)$_GET['router'] : (isset($_SESSION['router']) ? (int)$_SESSION['router'] : null);
        if ($routerId !== null && isset($routers[$routerId])) {
            $_SESSION['router'] = $routerId;
            return $routerId;
        }
        return null;
    }
}

if (!function_exists('cas_load_dashboard_data')) {
    function cas_load_dashboard_data($routerId, $routers) {
        $result = [
            'router' => ($routerId !== null && isset($routers[$routerId])) ? $routers[$routerId] : null,
            'secrets' => [],
            'active' => [],
            'profiles' => [],
            'total' => 0,
            'active_total' => 0,
            'isolir_total' => 0,
            'cached' => false,
            'cache_ttl' => 0,
            'error' => '',
        ];

        if ($routerId === null || empty($result['router'])) {
            return $result;
        }

        $payload = ['router_id' => $routerId];
        $dashboardRes = bridge_post('/dashboard-summary', $payload);
        if (!empty($dashboardRes['ok'])) {
            $result['secrets'] = $dashboardRes['secrets'] ?? [];
            $result['active'] = $dashboardRes['active'] ?? [];
            $result['profiles'] = $dashboardRes['profiles'] ?? [];
            $result['cached'] = !empty($dashboardRes['cached']);
            $result['cache_ttl'] = (int)($dashboardRes['cache_ttl'] ?? 0);
        } else {
            // Fallback aman jika bridge lama belum punya dashboard-summary.
            $secretsRes = bridge_post('/ppp/secrets', $payload);
            $activeRes = bridge_post('/ppp/active', $payload);
            $profilesRes = bridge_post('/ppp/profiles', $payload);
            if (!empty($secretsRes['ok'])) { $result['secrets'] = $secretsRes['data'] ?? []; }
            if (!empty($activeRes['ok'])) { $result['active'] = $activeRes['data'] ?? []; }
            if (!empty($profilesRes['ok'])) { $result['profiles'] = $profilesRes['data'] ?? []; }
            if (empty($secretsRes['ok'])) {
                $result['error'] = $secretsRes['detail'] ?? $secretsRes['error'] ?? $dashboardRes['detail'] ?? $dashboardRes['error'] ?? 'Gagal mengambil data dari bridge.';
            }
        }

        $result['total'] = count($result['secrets']);
        $result['active_total'] = count($result['active']);
        foreach ($result['secrets'] as $user) {
            if (($user['profile'] ?? '') === 'ISOLIREBILLING') { $result['isolir_total']++; }
        }
        return $result;
    }
}

if (!function_exists('cas_cache_badge')) {
    function cas_cache_badge($data) {
        if (empty($data['cache_ttl'])) { return ''; }
        $label = !empty($data['cached']) ? 'Cache aktif' : 'Data fresh';
        $class = !empty($data['cached']) ? ' cached' : '';
        return '<span class="cas-cache-pill' . $class . '">' . h($label) . ' · ' . h($data['cache_ttl']) . ' dtk</span>';
    }
}
?>
