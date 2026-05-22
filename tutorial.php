<?php
require('auth.php');
require('includes/_layout.php');
$GLOBALS['cas_current_page']='tutorial';
cas_page_start(['page'=>'tutorial','title'=>'Tutorial CAS','subtitle'=>'Panduan penggunaan panel PPPoE dan MikroTik bridge.','kicker'=>'Panduan Operator','icon'=>'📘']);
?>
<div class="cas-card cas-card-lg mb-3"><span class="cas-page-kicker">🛡️ CAS = Client Access Control</span><h2 class="cas-card-title">Alur Kerja Sistem</h2><p class="cas-card-muted">Hosting CAS tidak konek langsung ke MikroTik. Panel mengirim perintah ke bridge Ubuntu, lalu bridge meneruskan ke RouterOS API. Ini membuat akses lebih stabil dan aman untuk router yang tidak bisa dijangkau langsung dari hosting.</p></div>
<div class="cas-grid">
    <div class="cas-card" style="grid-column:span 6"><h4 class="cas-card-title">1. Menambahkan Router</h4><ol class="cas-help-list"><li>Buka menu <span class="cas-kbd">Router</span>.</li><li>Isi nama router, IP/host, username, password, dan <strong>port API custom</strong>.</li><li>Jika router memakai API SSL, aktifkan switch SSL.</li><li>Klik <strong>Simpan Router ke Bridge</strong>.</li><li>Klik tombol <strong>Test</strong> untuk memastikan identity MikroTik terbaca.</li></ol></div>
    <div class="cas-card" style="grid-column:span 6"><h4 class="cas-card-title">2. Memakai Dashboard PPPoE</h4><ol class="cas-help-list"><li>Buka dashboard.</li><li>Pilih router dari dropdown.</li><li>Klik <strong>Koneksikan</strong>.</li><li>Panel menampilkan total secret, user aktif, profile, dan daftar isolir.</li></ol></div>
    <div class="cas-card" style="grid-column:span 6"><h4 class="cas-card-title">3. Isolir / Buka Isolir</h4><ul class="cas-help-list"><li>Tombol <strong>Isolasi</strong> mengubah profile user ke <span class="cas-kbd">ISOLIREBILLING</span> lalu memutus sesi aktif.</li><li>Untuk buka isolir, pilih profile normal dari dropdown lalu klik <strong>Ubah</strong>.</li><li>User aktif otomatis diputus supaya profile baru langsung berlaku.</li></ul></div>
    <div class="cas-card" style="grid-column:span 6"><h4 class="cas-card-title">4. Kelola VLAN</h4><ul class="cas-help-list"><li>Buka menu <span class="cas-kbd">VLAN</span> setelah router terkoneksi.</li><li>Data VLAN diambil via bridge.</li><li>Gunakan tombol Enable/Disable dengan hati-hati karena langsung mengubah interface RouterOS.</li></ul></div>
    <div class="cas-card" style="grid-column:span 12"><h4 class="cas-card-title">Catatan Aman</h4><ul class="cas-help-list"><li>Port API tidak harus sama antar router. Isi sesuai NAT/firewall masing-masing router.</li><li>Simpan kredensial MikroTik hanya dari halaman Router; data disimpan di bridge Ubuntu.</li><li>Pastikan IP Ubuntu bridge diizinkan pada firewall MikroTik.</li><li>Jika test gagal, cek IP, port, username, password, SSL, dan rule firewall RouterOS.</li></ul></div>
</div>
<?php cas_page_end(); ?>
