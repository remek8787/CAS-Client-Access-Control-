<?php
require('auth.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tutorial CAS - Client Access Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets_cas.css?v=20260505-ux1" rel="stylesheet">
</head>
<body class="cas-body">
<div class="cas-shell">
    <div class="cas-topbar">
        <div class="cas-brand"><div class="cas-logo">📘</div><div><h1 class="cas-title">Tutorial CAS</h1><p class="cas-subtitle">Client Access Control · Panduan penggunaan panel PPPoE dan MikroTik bridge</p></div></div>
        <div class="cas-actions"><a href="index.php" class="btn btn-light">Dashboard</a><a href="manage_routers.php" class="btn btn-outline-light">Router</a><a href="tambah_user.php" class="btn btn-outline-light">Pengguna</a><a href="logout.php" class="btn btn-danger">Logout</a></div>
    </div>

    <div class="cas-hero">
        <span class="cas-badge">🛡️ CAS = Client Access Control</span>
        <h2>Alur Kerja Sistem</h2>
        <p>Hosting CAS tidak konek langsung ke MikroTik. Panel mengirim perintah ke bridge Ubuntu, lalu bridge yang meneruskan ke RouterOS API. Ini membuat akses lebih stabil dan aman untuk router yang tidak bisa dijangkau langsung dari hosting.</p>
    </div>

    <div class="cas-grid">
        <div class="cas-card" style="grid-column:span 6"><h4>1. Menambahkan Router</h4><ol class="cas-help-list"><li>Buka menu <span class="cas-kbd">Router</span>.</li><li>Isi nama router, IP/host, username, password, dan <strong>port API custom</strong>.</li><li>Jika router memakai API SSL, aktifkan switch SSL.</li><li>Klik <strong>Simpan Router ke Bridge</strong>.</li><li>Klik tombol <strong>Test</strong> untuk memastikan identity MikroTik terbaca.</li></ol></div>
        <div class="cas-card" style="grid-column:span 6"><h4>2. Memakai Dashboard PPPoE</h4><ol class="cas-help-list"><li>Buka dashboard.</li><li>Pilih router dari dropdown.</li><li>Klik <strong>Koneksikan</strong>.</li><li>Panel akan menampilkan total secret, user aktif, profile, dan daftar isolir.</li></ol></div>
        <div class="cas-card" style="grid-column:span 6"><h4>3. Isolir / Buka Isolir</h4><ul class="cas-help-list"><li>Tombol <strong>Isolasi</strong> mengubah profile user ke <span class="cas-kbd">ISOLIREBILLING</span> lalu memutus sesi aktif.</li><li>Untuk buka isolir, pilih profile normal dari dropdown lalu klik <strong>Ubah</strong>.</li><li>User aktif akan otomatis diputus supaya profile baru langsung berlaku.</li></ul></div>
        <div class="cas-card" style="grid-column:span 6"><h4>4. Kelola VLAN</h4><ul class="cas-help-list"><li>Buka menu <span class="cas-kbd">Kelola VLAN</span> setelah router terkoneksi.</li><li>Data VLAN diambil via bridge.</li><li>Gunakan tombol Enable/Disable dengan hati-hati karena langsung mengubah interface RouterOS.</li></ul></div>
        <div class="cas-card" style="grid-column:span 12"><h4>Catatan Aman</h4><ul class="cas-help-list"><li>Port API tidak harus sama antar router. Isi sesuai NAT/firewall masing-masing router.</li><li>Simpan kredensial MikroTik hanya dari halaman Router; data akan disimpan di bridge Ubuntu, bukan dipakai untuk koneksi langsung dari hosting.</li><li>Pastikan IP Ubuntu bridge diizinkan pada firewall MikroTik.</li><li>Jika test gagal, cek IP, port, username, password, SSL, dan rule firewall RouterOS.</li></ul></div>
    </div>
    <p class="cas-footer-note">CAS v1.1 · Client Access Control · Bridge Mode</p>
</div>
</body>
</html>
