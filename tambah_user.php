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
            'password' => $u['password'] ?? '',
            'role' => $u['role'] ?? 'admin',
            'protected' => (($u['username'] ?? '') === 'ananta')
        ];
    }
    return $out;
}

function valid_username($username) {
    return preg_match('/^[a-z0-9._-]{3,32}$/', $username);
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

        if ($newUser === '' || $newPass === '') json_out(['ok' => false, 'error' => 'Username dan password wajib diisi.']);
        if (!valid_username($newUser)) json_out(['ok' => false, 'error' => 'Username 3-32 karakter, gunakan huruf/angka/titik/strip/underscore.']);
        foreach ($users as $u) if (($u['username'] ?? '') === $newUser) json_out(['ok' => false, 'error' => 'Username sudah digunakan.']);

        $users[] = ['username' => $newUser, 'password' => $newPass, 'role' => $newRole];
        save_users($usersFile, $users);
        json_out(['ok' => true, 'message' => "User '$newUser' berhasil ditambahkan.", 'users' => public_users($users)]);
    }

    if ($action === 'update') {
        $oldUser = strtolower(trim($_POST['old_username'] ?? ''));
        $newUser = strtolower(trim($_POST['username'] ?? ''));
        $newPass = trim($_POST['password'] ?? '');
        $newRole = trim($_POST['role'] ?? 'admin');
        if ($newRole !== 'admin' && $newRole !== 'viewer' && $newRole !== 'superadmin') $newRole = 'admin';
        if ($oldUser === 'ananta' && $newUser !== 'ananta') json_out(['ok' => false, 'error' => "Username 'ananta' tidak boleh diganti."]);
        if ($newUser === '' || $newPass === '') json_out(['ok' => false, 'error' => 'Username dan password wajib diisi.']);
        if (!valid_username($newUser)) json_out(['ok' => false, 'error' => 'Username 3-32 karakter, gunakan huruf/angka/titik/strip/underscore.']);

        $found = false;
        foreach ($users as $idx => $u) {
            if (($u['username'] ?? '') === $newUser && $newUser !== $oldUser) json_out(['ok' => false, 'error' => 'Username baru sudah digunakan.']);
        }
        foreach ($users as $idx => $u) {
            if (($u['username'] ?? '') === $oldUser) {
                $users[$idx] = ['username' => $newUser, 'password' => $newPass, 'role' => $newRole];
                $found = true;
                break;
            }
        }
        if (!$found) json_out(['ok' => false, 'error' => 'User tidak ditemukan.']);
        save_users($usersFile, $users);
        json_out(['ok' => true, 'message' => "User '$newUser' berhasil diedit.", 'users' => public_users($users)]);
    }

    if ($action === 'delete') {
        $hapus = strtolower(trim($_POST['username'] ?? ''));
        if ($hapus === 'ananta') json_out(['ok' => false, 'error' => "User 'ananta' tidak boleh dihapus."]);
        $before = count($users);
        $filtered = [];
        foreach ($users as $u) if (($u['username'] ?? '') !== $hapus) $filtered[] = $u;
        if (count($filtered) === $before) json_out(['ok' => false, 'error' => 'User tidak ditemukan.']);
        save_users($usersFile, $filtered);
        json_out(['ok' => true, 'message' => "User '$hapus' berhasil dihapus.", 'users' => public_users($filtered)]);
    }

    json_out(['ok' => false, 'error' => 'Aksi tidak dikenal.']);
}

$users = load_users($usersFile);
?>
<?php require('includes/_layout.php'); $GLOBALS['cas_current_page']='users'; cas_page_start(['page'=>'users','title'=>'User CAS','subtitle'=>'Tambah, lihat password, edit role, dan hapus akun operator CAS.','kicker'=>'User Management','icon'=>'👥']); ?>

<section class="cas-card cas-card-lg mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
        <div><span id="modeBadge" class="cas-page-kicker">Tambah User</span><h2 id="formTitle" class="cas-card-title">Tambah User</h2><p class="cas-card-muted">Kelola akun login operator CAS. Password tetap ditampilkan sesuai kebutuhan admin.</p></div>
        <div id="toast" class="cas-alert cas-alert-info d-none mb-0"></div>
    </div>
    <form id="userForm" class="row g-3 mt-2" data-no-loader="1">
        <input type="hidden" name="ajax" value="1"><input type="hidden" name="action" value="add" id="formAction"><input type="hidden" name="old_username" id="oldUsername">
        <div class="col-md-3"><label class="fw-bold mb-1">Username</label><input name="username" id="username" class="form-control" placeholder="operator1" required></div>
        <div class="col-md-3"><label class="fw-bold mb-1">Password</label><input name="password" id="password" type="text" class="form-control" placeholder="password" required></div>
        <div class="col-md-3"><label class="fw-bold mb-1">Role</label><select name="role" id="role" class="form-select"><option value="admin">Admin</option><option value="viewer">Viewer</option><option value="superadmin">Superadmin</option></select></div>
        <div class="col-md-3 d-flex align-items-end gap-2"><button id="submitBtn" class="btn btn-primary flex-fill">Tambah User</button><button type="button" id="cancelBtn" class="btn btn-outline-secondary d-none">Batal</button></div>
    </form>
</section>

<section class="cas-card cas-table-card"><div class="cas-table-head cas-grad-dark"><h5>Daftar User</h5><span id="userCount"><?= count($users) ?> user</span></div><div class="cas-table-wrap"><table class="table table-hover align-middle"><thead><tr><th>Username</th><th>Password</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody id="usersBody"></tbody></table></div></section>

<script>
const initialUsers=<?= json_encode(public_users($users), JSON_UNESCAPED_UNICODE) ?>;
const body=document.getElementById('usersBody'), count=document.getElementById('userCount'), toast=document.getElementById('toast'), form=document.getElementById('userForm'), formAction=document.getElementById('formAction'), oldUsername=document.getElementById('oldUsername'), username=document.getElementById('username'), password=document.getElementById('password'), role=document.getElementById('role'), submitBtn=document.getElementById('submitBtn'), cancelBtn=document.getElementById('cancelBtn'), formTitle=document.getElementById('formTitle'), modeBadge=document.getElementById('modeBadge');
function esc(s){return String(s||'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function showToast(ok,msg){toast.className='cas-alert mb-0 '+(ok?'cas-alert-success':'cas-alert-danger');toast.textContent=(ok?'✅ ':'❌ ')+msg;toast.classList.remove('d-none');setTimeout(()=>toast.classList.add('d-none'),4500);}
function resetForm(){form.reset();formAction.value='add';oldUsername.value='';submitBtn.textContent='Tambah User';formTitle.textContent='Tambah User';modeBadge.textContent='Tambah User';cancelBtn.classList.add('d-none');username.disabled=false;}
function editUser(u){formAction.value='update';oldUsername.value=u.username;username.value=u.username;password.value=u.password;role.value=u.role;submitBtn.textContent='Simpan Edit';formTitle.textContent='Edit User';modeBadge.textContent='Edit User';cancelBtn.classList.remove('d-none');username.disabled=false;window.scrollTo({top:0,behavior:'smooth'});}
function render(users){count.textContent=users.length+' user';body.innerHTML=users.map((u,i)=>`<tr><td><strong>${esc(u.username)}</strong></td><td><code class="cas-kbd">${esc(u.password)}</code></td><td><span class="badge text-bg-primary">${esc(u.role)}</span></td><td>${u.protected?'<span class="badge text-bg-warning">Protected</span>':'<span class="badge text-bg-success">Editable</span>'}</td><td class="text-end"><div class="d-flex justify-content-end gap-2 flex-wrap">${u.protected?'<span class="text-muted fw-bold">Tidak bisa hapus</span>':`<button onclick='editUser(currentUsers[${i}])' class="btn btn-sm btn-outline-warning">Edit</button><button onclick="delUser('${esc(u.username)}')" class="btn btn-sm btn-outline-danger">Hapus</button>`}</div></td></tr>`).join('');currentUsers=users;}
let currentUsers=[];async function post(fd){const r=await fetch('tambah_user.php',{method:'POST',body:fd});return await r.json();}
form.addEventListener('submit',async e=>{e.preventDefault();username.disabled=false;submitBtn.disabled=true;const old=submitBtn.textContent;submitBtn.textContent='Memproses...';const fd=new FormData(form);const res=await post(fd);submitBtn.disabled=false;submitBtn.textContent=old;showToast(!!res.ok,res.message||res.error||'Selesai');if(res.ok){render(res.users);resetForm();}});
cancelBtn.addEventListener('click',resetForm);
async function delUser(user){if(!confirm('Hapus user '+user+'?'))return;const fd=new FormData();fd.append('ajax','1');fd.append('action','delete');fd.append('username',user);const res=await post(fd);showToast(!!res.ok,res.message||res.error||'Selesai');if(res.ok)render(res.users);}render(initialUsers);
</script>
<?php cas_page_end(); ?>
