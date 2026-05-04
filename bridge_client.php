<?php
// CAS MikroTik Bridge Client
// Panel hosting -> Ubuntu bridge -> MikroTik
// Copy this file to bridge_client.php and set the live token/base URL on hosting.

define('BRIDGE_BASE_URL', getenv('CAS_BRIDGE_BASE_URL') ?: 'http://YOUR_UBUNTU_IP/cas-bridge');
define('BRIDGE_TOKEN', getenv('CAS_BRIDGE_TOKEN') ?: 'CHANGE_ME_BRIDGE_TOKEN');

function bridge_request($method, $path, $payload = null) {
    $url = rtrim(BRIDGE_BASE_URL, '/') . '/' . ltrim($path, '/');
    $headers = "Content-Type: application/json\r\nX-Bridge-Token: " . BRIDGE_TOKEN . "\r\n";
    $options = [
        'http' => [
            'method' => strtoupper($method),
            'timeout' => 45,
            'ignore_errors' => true,
            'header' => $headers,
        ]
    ];
    if ($payload !== null) {
        $options['http']['content'] = json_encode($payload);
    }
    $context = stream_context_create($options);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'bridge_unreachable', 'url' => $url];
    }
    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ['ok' => false, 'error' => 'bridge_invalid_json', 'raw' => $raw];
    }
    return $json;
}

function bridge_get($path) { return bridge_request('GET', $path); }
function bridge_post($path, $payload = []) { return bridge_request('POST', $path, $payload); }
function bridge_routers() { $res = bridge_get('/routers'); return !empty($res['ok']) ? ($res['data'] ?? []) : []; }
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
