---
title: "Blueprint — CAS Client Access Control"
description: "Acuan project CAS untuk kontrol akses pelanggan PPPoE via bridge Ubuntu menuju MikroTik RouterOS API."
project: cas, dsg, mikrotik
created: "2026-05-05"
updated: "2026-05-05"
tags: [cas, client-access-control, mikrotik, pppoe, bridge, routeros, php, ubuntu]
---

# Blueprint — CAS Client Access Control

CAS adalah **Client Access Control**, panel hosting PHP untuk mengelola akses pelanggan PPPoE melalui bridge Ubuntu menuju MikroTik RouterOS API.

## Arsitektur

```text
Hosting CAS PHP -> Ubuntu Bridge API -> MikroTik RouterOS API
```

- Hosting CAS hanya menjadi UI/panel.
- Ubuntu bridge berada di `/home/ananta/apps/cas-mikrotik-bridge`.
- Service bridge: `cas-mikrotik-bridge.service`.
- Bridge listen lokal di `127.0.0.1:9077` dan dipublish nginx lewat `/cas-bridge/`.
- Public proxy/bridge URL aktif memakai IP publik Ubuntu: `http://103.196.85.88/cas-bridge/`.
- Health check publik: `GET http://103.196.85.88/cas-bridge/health` mengembalikan JSON `{"ok": true, "service": "cas-mikrotik-bridge", "version": "1.0"}`.
- Catatan: endpoint bridge mendukung `GET/POST` sesuai route; `HEAD` ke root bisa `501` dan tidak dianggap gagal.
- Service lain di Ubuntu, terutama Ookla `8080/5060` dan Hermes `9119`, tidak boleh disentuh kecuali ada instruksi eksplisit.

## Live Context

- Hosting: `https://cas.dentasejahteragroup.my.id/`
- Ubuntu bridge/server proxy: `103.196.85.88`
- Public bridge path: `http://103.196.85.88/cas-bridge/`
- Router aktif awal: `RO DSG FROM XL`
- MikroTik target awal: `103.196.85.2:29301`
- Identity terbaca: `RO-DSG-CORE SMKL`

## Fitur Live

- Dashboard PPPoE
- List PPP Secret
- List PPP Active
- Ubah profile PPPoE
- Isolir ke `ISOLIREBILLING`
- Disconnect user aktif
- VLAN enable/disable
- Router CRUD via web
- Custom API port per router
- Toggle API SSL per router
- Tutorial bawaan
- User CAS:
  - tambah user
  - lihat password
  - edit username/password/role
  - hapus user non-protected
  - user `ananta` protected dari hapus

## Copywriting Dashboard

Gunakan copy ini, bukan copy teknis panjang soal hosting/bridge:

> Kelola akses pelanggan PPPoE dari satu panel: pantau user aktif, ubah profile, isolir, buka isolir, disconnect user, dan kontrol VLAN/router dengan cepat.

## Security Rules

- Jangan push live `users.json`, `routers.json`, `.env`, token bridge, password router, atau log ke GitHub.
- Hosting harus memblokir akses langsung ke `.json`, `.txt`, `.log`, `.bak`, dan `.zip` via `.htaccess`.
- GitHub hanya menyimpan source sanitized + contoh `.example`.
- Jika perlu melakukan test add/edit/delete user, gunakan user sementara lalu hapus kembali.

## GitHub

Repo backup/source:

```text
git@github.com:remek8787/CAS-Client-Access-Control-.git
```

Branch utama: `main`.

## Verification Checklist

- `login.php` HTTP 200.
- `index.php` HTTP 200 setelah login.
- `tambah_user.php` HTTP 200.
- `tambah_user.php?ajax=1` JSON `ok:true`.
- AJAX add/edit/delete user sementara sukses dan user sementara dihapus.
- `users.json` dan `routers.json` HTTP 403 dari publik.
- Bridge service active: `systemctl is-active cas-mikrotik-bridge.service`.
- Public bridge health via IP publik returns ok: `curl http://103.196.85.88/cas-bridge/health`.
