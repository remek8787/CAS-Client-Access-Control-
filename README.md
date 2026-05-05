# CAS — Client Access Control

CAS adalah panel ringan untuk mengelola akses pelanggan PPPoE melalui MikroTik RouterOS API dengan pola bridge.

Arsitektur live:

```text
Hosting CAS PHP -> Ubuntu Bridge API -> MikroTik RouterOS API
```

## Deskripsi Dashboard

Kelola akses pelanggan PPPoE dari satu panel: pantau user aktif, ubah profile, isolir, buka isolir, disconnect user, dan kontrol VLAN/router dengan cepat.

## Fitur

- Dashboard PPPoE modern
- List PPP Secret dan PPP Active
- Ubah profile PPPoE
- Isolir user ke `ISOLIREBILLING`
- Disconnect active user
- Kelola VLAN enable/disable
- Router management via web
- Custom API port per router
- Tutorial bawaan
- UI modern dengan Bootstrap/Tailwind CDN
- Halaman **User CAS**:
  - tambah user
  - lihat password user
  - edit username/password/role
  - hapus user non-protected
  - AJAX ringan tanpa reload penuh

## Struktur

- Root PHP files: panel hosting
- `bridge/bridge_server.py`: bridge API Python untuk Ubuntu server
- `bridge/.env.example`: contoh environment bridge
- `users.json.example`: contoh user login panel
- `routers.json.example`: contoh config router bridge

## Catatan Security

File live berikut tidak dipush ke GitHub:

- `users.json`
- `routers.json`
- `log_aktivitas.txt`
- `.env`
- token bridge/password live

Gunakan `.htaccess` di hosting untuk mencegah akses langsung file sensitif.

## Deploy Ringkas

1. Upload file panel PHP ke hosting.
2. Buat `users.json` dari `users.json.example`.
3. Pasang bridge di Ubuntu dari folder `bridge/`.
4. Buat `.env` dari `bridge/.env.example`.
5. Set `bridge_client.php` dengan base URL dan token live.
6. Pastikan MikroTik mengizinkan IP Ubuntu bridge mengakses port API.
