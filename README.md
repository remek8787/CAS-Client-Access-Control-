# CAS — Client Access Control

CAS adalah panel PHP ringan untuk kontrol akses pelanggan PPPoE via MikroTik RouterOS API.

Arsitektur live:

```text
Hosting CAS PHP -> Ubuntu Bridge API -> MikroTik RouterOS API
```

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
- UI modern dengan Bootstrap/Tailwind CDN dan AJAX ringan pada halaman pengguna

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
