<?php
require('auth.php');

$usersFile = 'users.json';

function load_users($file) {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_users($file, $users) {
    file_put_contents($file, json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function json_out($payload) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function public_users($users) {
    $out = [];
    foreach ($users as $u) {
        $out[] = [
            'username' => $u['username'] ?? '',
            'role' => $u['role'] ?? 'admin',
            'protected' => (($u['username'] ?? '') === 'ananta')
        ];
    }
    return $out;
}

if (isset($_GET['ajax'])) {
    $users = load_users($usersFile);
    json_out(['ok' => true, 'users' => public_users($users)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $users = load_users($usersFile);
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $newUser = strtolower(trim($_POST['username'] ?? ''));
        $newPass = trim($_POST['password'] ?? '');
        $newRole = trim($_POST['role'] ?? 'admin');
        if ($newRole !== 'admin' && $newRole !== 'viewer' && $newRole !== 'superadmin') $newRole = 'admin';

        if ($newUser === '' || $newPass === '') {
            json_out(['ok' => false, 'error' => 'Username dan password wajib diisi.']);
        }
        if (!preg_match('/^[a-z0-9._-]{3,32}$/', $newUser)) {
            json_out(['ok' => false, 'error' => 'Username 3-32 karakter, gunakan huruf/angka/titik/strip/underscore.']);
        }
        foreach ($users as $u) {
            if (($u['username'] ?? '') === $newUser) {
                json_out(['ok' => false, 'error' => 'Username sudah digunakan.']);
            }
        }
        $users[] = ['username' => $newUser, 'password' => $newPass, 'role' => $newRole];
        save_users($usersFile, $users);
        json_out(['ok' => true, 'message' => "Pengguna '$newUser' berhasil ditambahkan.", 'users' => public_users($users)]);
    }

    if ($action === 'delete') {
        $hapus = strtolower(trim($_POST['username'] ?? ''));
        if ($hapus === 'ananta') {
            json_out(['ok' => false, 'error' => "User 'ananta' tidak boleh dihapus."]);
        }
        $before = count($users);
        $filtered = [];
        foreach ($users as $u) {
            if (($u['username'] ?? '') !== $hapus) $filtered[] = $u;
        }
        if (count($filtered) === $before) {
            json_out(['ok' => false, 'error' => 'User tidak ditemukan.']);
        }
        save_users($usersFile, $filtered);
        json_out(['ok' => true, 'message' => "Pengguna '$hapus' berhasil dihapus.", 'users' => public_users($filtered)]);
    }

    json_out(['ok' => false, 'error' => 'Aksi tidak dikenal.']);
}

$users = load_users($usersFile);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAS Pengguna - Client Access Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { cas: { 900:'#07111f', 800:'#0f172a', blue:'#2563eb', cyan:'#06b6d4' } } } } };
    </script>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(37,99,235,.35),transparent_32%),linear-gradient(135deg,#07111f,#0f172a_55%,#111827)] text-slate-900">
<div class="max-w-7xl mx-auto px-4 py-7">
    <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 text-white">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-400 grid place-items-center text-3xl shadow-2xl">👥</div>
            <div>
                <h1 class="text-3xl font-black tracking-tight">CAS Pengguna</h1>
                <p class="text-slate-300">Client Access Control · User login panel berbasis AJAX ringan</p>
            </div>
        </div>
        <nav class="flex flex-wrap gap-2">
            <a href="index.php" class="px-4 py-2 rounded-xl bg-white text-slate-900 font-bold hover:bg-slate-100">Dashboard</a>
            <a href="manage_routers.php" class="px-4 py-2 rounded-xl border border-white/25 text-white font-bold hover:bg-white/10">Router</a>
            <a href="tutorial.php" class="px-4 py-2 rounded-xl border border-white/25 text-white font-bold hover:bg-white/10">Tutorial</a>
            <a href="logout.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700">Logout</a>
        </nav>
    </header>

    <section class="rounded-[28px] bg-white/95 backdrop-blur shadow-2xl border border-white/70 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-black mb-3">Tambah pengguna</span>
                <h2 class="text-2xl font-black tracking-tight">Buat akun operator CAS</h2>
                <p class="text-slate-500">Data login masih mengikuti format project lama agar kompatibel dengan hosting XAMPP legacy.</p>
            </div>
            <div id="toast" class="hidden px-4 py-3 rounded-2xl font-bold"></div>
        </div>
        <form id="addForm" class="grid md:grid-cols-12 gap-3 mt-5">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="action" value="add">
            <div class="md:col-span-3"><label class="text-sm font-black text-slate-600">Username</label><input name="username" class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400" placeholder="operator1" required></div>
            <div class="md:col-span-3"><label class="text-sm font-black text-slate-600">Password</label><input name="password" type="password" class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400" placeholder="password" required></div>
            <div class="md:col-span-3"><label class="text-sm font-black text-slate-600">Role</label><select name="role" class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-400"><option value="admin">Admin</option><option value="viewer">Viewer</option><option value="superadmin">Superadmin</option></select></div>
            <div class="md:col-span-3 flex items-end"><button class="w-full rounded-2xl bg-blue-600 text-white font-black px-5 py-3 hover:bg-blue-700 shadow-lg shadow-blue-600/25">Tambah via AJAX</button></div>
        </form>
    </section>

    <section class="rounded-[28px] bg-white/95 backdrop-blur shadow-2xl border border-white/70 overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-black text-lg">Daftar Pengguna</h3>
            <span id="userCount" class="text-sm text-slate-300"><?= count($users) ?> user</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider"><tr><th class="px-6 py-3">Username</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Status</th><th class="px-6 py-3 text-right">Aksi</th></tr></thead>
                <tbody id="usersBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </section>
</div>
<script>
const initialUsers = <?= json_encode(public_users($users), JSON_UNESCAPED_UNICODE) ?>;
const body = document.getElementById('usersBody');
const count = document.getElementById('userCount');
const toast = document.getElementById('toast');
function esc(s){return String(s||'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function showToast(ok,msg){toast.className='px-4 py-3 rounded-2xl font-bold '+(ok?'bg-emerald-50 text-emerald-700':'bg-red-50 text-red-700');toast.textContent=(ok?'✅ ':'❌ ')+msg;toast.classList.remove('hidden');setTimeout(()=>toast.classList.add('hidden'),4500);}
function render(users){count.textContent=users.length+' user';body.innerHTML=users.map(u=>`<tr class="hover:bg-slate-50"><td class="px-6 py-4 font-black text-slate-900">${esc(u.username)}</td><td class="px-6 py-4"><span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-black">${esc(u.role)}</span></td><td class="px-6 py-4 text-slate-500">${u.protected?'Protected':'Editable'}</td><td class="px-6 py-4 text-right">${u.protected?'<span class="text-slate-400 font-bold">Dikunci</span>':`<button onclick="delUser('${esc(u.username)}')" class="px-3 py-2 rounded-xl bg-red-50 text-red-700 font-black hover:bg-red-100">Hapus</button>`}</td></tr>`).join('');}
async function post(fd){const r=await fetch('tambah_user.php',{method:'POST',body:fd});return await r.json();}
document.getElementById('addForm').addEventListener('submit',async e=>{e.preventDefault();const fd=new FormData(e.target);const res=await post(fd);showToast(!!res.ok,res.message||res.error||'Selesai');if(res.ok){render(res.users);e.target.reset();}});
async function delUser(username){if(!confirm('Hapus user '+username+'?'))return;const fd=new FormData();fd.append('ajax','1');fd.append('action','delete');fd.append('username',username);const res=await post(fd);showToast(!!res.ok,res.message||res.error||'Selesai');if(res.ok)render(res.users);} 
render(initialUsers);
</script>
</body>
</html>
