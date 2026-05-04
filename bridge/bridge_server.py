#!/usr/bin/env python3
import json
import os
import socket
import ssl
import traceback
from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import urlparse

BASE = os.environ.get('BRIDGE_BASE', '/home/ananta/apps/cas-mikrotik-bridge')
ROUTERS_FILE = os.path.join(BASE, 'routers.json')
TOKEN = os.environ.get('BRIDGE_TOKEN', 'change-me-now')
ALLOWED_ORIGINS = [o.strip() for o in os.environ.get('ALLOWED_ORIGINS', '*').split(',') if o.strip()]
ISOLATE_PROFILE = os.environ.get('ISOLATE_PROFILE', 'ISOLIREBILLING')

class RouterOS:
    def __init__(self, host, user, password, port=8728, use_ssl=False, timeout=8):
        self.host = host
        self.user = user
        self.password = password
        self.port = int(port)
        self.use_ssl = bool(use_ssl)
        self.timeout = timeout
        self.sock = None

    def connect(self):
        raw = socket.create_connection((self.host, self.port), self.timeout)
        raw.settimeout(self.timeout)
        if self.use_ssl:
            ctx = ssl.create_default_context()
            ctx.check_hostname = False
            ctx.verify_mode = ssl.CERT_NONE
            self.sock = ctx.wrap_socket(raw, server_hostname=self.host)
        else:
            self.sock = raw
        self.talk(['/login', f'=name={self.user}', f'=password={self.password}'])
        return True

    def close(self):
        try:
            if self.sock:
                self.sock.close()
        finally:
            self.sock = None

    def enc_len(self, length):
        if length < 0x80:
            return bytes([length])
        if length < 0x4000:
            length |= 0x8000
            return length.to_bytes(2, 'big')
        if length < 0x200000:
            length |= 0xC00000
            return length.to_bytes(3, 'big')
        if length < 0x10000000:
            length |= 0xE0000000
            return length.to_bytes(4, 'big')
        return bytes([0xF0]) + length.to_bytes(4, 'big')

    def read_exact(self, n):
        buf = b''
        while len(buf) < n:
            chunk = self.sock.recv(n - len(buf))
            if not chunk:
                raise ConnectionError('socket_closed')
            buf += chunk
        return buf

    def dec_len(self):
        c = self.read_exact(1)[0]
        if (c & 0x80) == 0x00:
            return c
        if (c & 0xC0) == 0x80:
            return ((c & ~0xC0) << 8) + self.read_exact(1)[0]
        if (c & 0xE0) == 0xC0:
            b = self.read_exact(2)
            return ((c & ~0xE0) << 16) + (b[0] << 8) + b[1]
        if (c & 0xF0) == 0xE0:
            b = self.read_exact(3)
            return ((c & ~0xF0) << 24) + (b[0] << 16) + (b[1] << 8) + b[2]
        b = self.read_exact(4)
        return int.from_bytes(b, 'big')

    def write_word(self, word):
        data = word.encode('utf-8')
        self.sock.sendall(self.enc_len(len(data)) + data)

    def read_word(self):
        length = self.dec_len()
        if length == 0:
            return ''
        return self.read_exact(length).decode('utf-8', errors='replace')

    def talk(self, words):
        for w in words:
            self.write_word(w)
        self.write_word('')
        replies = []
        sentence = []
        while True:
            word = self.read_word()
            if word == '':
                if sentence:
                    replies.append(sentence)
                    if sentence[0] == '!done':
                        break
                    sentence = []
                continue
            sentence.append(word)
        return self.parse(replies)

    def comm(self, command, params=None):
        words = [command]
        for k, v in (params or {}).items():
            words.append(f'{k}={v}')
        return self.talk(words)

    def parse(self, replies):
        result = []
        traps = []
        for sent in replies:
            typ = sent[0] if sent else ''
            if typ == '!re':
                row = {}
                for w in sent[1:]:
                    if w.startswith('='):
                        parts = w[1:].split('=', 1)
                        row[parts[0]] = parts[1] if len(parts) > 1 else ''
                result.append(row)
            elif typ in ('!trap', '!fatal'):
                row = {}
                for w in sent[1:]:
                    if w.startswith('='):
                        parts = w[1:].split('=', 1)
                        row[parts[0]] = parts[1] if len(parts) > 1 else ''
                traps.append(row or {'message': typ})
            elif typ == '!done':
                pass
        if traps:
            raise RuntimeError(json.dumps(traps, ensure_ascii=False))
        return result


def send_json(handler, code, data):
    body = json.dumps(data, ensure_ascii=False).encode('utf-8')
    handler.send_response(code)
    origin = handler.headers.get('Origin', '*')
    allow_origin = '*' if '*' in ALLOWED_ORIGINS else (origin if origin in ALLOWED_ORIGINS else ALLOWED_ORIGINS[0])
    handler.send_header('Content-Type', 'application/json; charset=utf-8')
    handler.send_header('Content-Length', str(len(body)))
    handler.send_header('Access-Control-Allow-Origin', allow_origin)
    handler.send_header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Bridge-Token')
    handler.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
    handler.end_headers()
    handler.wfile.write(body)


def load_routers():
    if not os.path.exists(ROUTERS_FILE):
        return []
    with open(ROUTERS_FILE, 'r', encoding='utf-8') as f:
        return json.load(f)


def save_routers(routers):
    tmp = ROUTERS_FILE + '.tmp'
    with open(tmp, 'w', encoding='utf-8') as f:
        json.dump(routers, f, indent=2, ensure_ascii=False)
    os.replace(tmp, ROUTERS_FILE)


def normalize_router(payload, existing=None):
    existing = existing or {}
    name = str(payload.get('name', existing.get('name', 'Router'))).strip()
    ip = str(payload.get('ip', existing.get('ip', ''))).strip()
    username = str(payload.get('username', existing.get('username', ''))).strip()
    password = str(payload.get('password', existing.get('password', ''))).strip()
    port = int(payload.get('port', existing.get('port', 8728)))
    use_ssl = bool(payload.get('ssl', existing.get('ssl', False)))
    if not name or not ip or not username or not password:
        raise ValueError('name/ip/username/password wajib diisi')
    if port < 1 or port > 65535:
        raise ValueError('port_invalid')
    return {'name': name, 'ip': ip, 'username': username, 'password': password, 'port': port, 'ssl': use_ssl}


def mask_router(router, idx):
    return {
        'id': idx,
        'name': router.get('name'),
        'ip': router.get('ip'),
        'port': int(router.get('port', 8728)),
        'username': router.get('username'),
        'ssl': bool(router.get('ssl', False)),
    }


def connect_router(router):
    api = RouterOS(
        router['ip'],
        router['username'],
        router['password'],
        int(router.get('port', 8728)),
        bool(router.get('ssl', False)),
    )
    api.connect()
    return api

class Handler(BaseHTTPRequestHandler):
    server_version = 'CASMikrotikBridge/1.0'

    def log_message(self, fmt, *args):
        return

    def do_OPTIONS(self):
        return send_json(self, 200, {'ok': True})

    def auth_ok(self):
        auth = self.headers.get('Authorization', '')
        token = self.headers.get('X-Bridge-Token') or (auth[7:] if auth.startswith('Bearer ') else '')
        return token == TOKEN

    def read_payload(self):
        length = int(self.headers.get('Content-Length', '0') or 0)
        raw = self.rfile.read(length) if length else b'{}'
        return json.loads(raw.decode('utf-8') or '{}')

    def do_GET(self):
        path = urlparse(self.path).path
        if path == '/health':
            return send_json(self, 200, {'ok': True, 'service': 'cas-mikrotik-bridge', 'version': '1.0'})
        if not self.auth_ok():
            return send_json(self, 401, {'ok': False, 'error': 'unauthorized'})
        if path == '/routers':
            routers = load_routers()
            return send_json(self, 200, {'ok': True, 'data': [mask_router(r, i) for i, r in enumerate(routers)]})
        return send_json(self, 404, {'ok': False, 'error': 'not_found'})

    def do_POST(self):
        path = urlparse(self.path).path
        if not self.auth_ok():
            return send_json(self, 401, {'ok': False, 'error': 'unauthorized'})
        api = None
        try:
            payload = self.read_payload()
            routers = load_routers()

            if path == '/routers/add':
                router = normalize_router(payload)
                routers.append(router)
                save_routers(routers)
                return send_json(self, 200, {'ok': True, 'message': 'router_added', 'router': mask_router(router, len(routers)-1)})

            if path == '/routers/update':
                rid = int(payload.get('router_id', -1))
                if rid < 0 or rid >= len(routers):
                    return send_json(self, 400, {'ok': False, 'error': 'router_not_found'})
                router = normalize_router(payload, routers[rid])
                routers[rid] = router
                save_routers(routers)
                return send_json(self, 200, {'ok': True, 'message': 'router_updated', 'router': mask_router(router, rid)})

            if path == '/routers/delete':
                rid = int(payload.get('router_id', -1))
                if rid < 0 or rid >= len(routers):
                    return send_json(self, 400, {'ok': False, 'error': 'router_not_found'})
                removed = routers.pop(rid)
                save_routers(routers)
                return send_json(self, 200, {'ok': True, 'message': 'router_deleted', 'router': mask_router(removed, rid)})

            rid = int(payload.get('router_id', 0))
            if rid < 0 or rid >= len(routers):
                return send_json(self, 400, {'ok': False, 'error': 'router_not_found'})
            router = routers[rid]
            api = connect_router(router)

            if path == '/test':
                ident = api.comm('/system/identity/print')
                return send_json(self, 200, {'ok': True, 'router': mask_router(router, rid), 'identity': ident})
            if path == '/ppp/secrets':
                data = api.comm('/ppp/secret/print')
                return send_json(self, 200, {'ok': True, 'count': len(data), 'data': data})
            if path == '/ppp/active':
                data = api.comm('/ppp/active/print')
                return send_json(self, 200, {'ok': True, 'count': len(data), 'data': data})
            if path == '/ppp/profiles':
                data = api.comm('/ppp/profile/print')
                return send_json(self, 200, {'ok': True, 'count': len(data), 'data': data})
            if path == '/ppp/change-profile':
                username = payload['username']
                profile = payload['profile']
                secrets = api.comm('/ppp/secret/print', {'?name': username})
                if not secrets:
                    return send_json(self, 404, {'ok': False, 'error': 'user_not_found'})
                api.comm('/ppp/secret/set', {'.id': secrets[0]['.id'], 'profile': profile})
                active = api.comm('/ppp/active/print', {'?name': username})
                if active:
                    api.comm('/ppp/active/remove', {'.id': active[0]['.id']})
                return send_json(self, 200, {'ok': True, 'message': 'profile_updated_and_user_kicked'})
            if path == '/ppp/isolate':
                username = payload['username']
                profile = payload.get('profile', ISOLATE_PROFILE)
                secrets = api.comm('/ppp/secret/print', {'?name': username})
                if not secrets:
                    return send_json(self, 404, {'ok': False, 'error': 'user_not_found'})
                api.comm('/ppp/secret/set', {'.id': secrets[0]['.id'], 'profile': profile})
                active = api.comm('/ppp/active/print', {'?name': username})
                if active:
                    api.comm('/ppp/active/remove', {'.id': active[0]['.id']})
                return send_json(self, 200, {'ok': True, 'message': 'user_isolated'})
            if path == '/ppp/disconnect':
                api.comm('/ppp/active/remove', {'.id': payload['id']})
                return send_json(self, 200, {'ok': True, 'message': 'user_disconnected'})
            if path == '/vlan/list':
                data = api.comm('/interface/vlan/print')
                return send_json(self, 200, {'ok': True, 'count': len(data), 'data': data})
            if path == '/vlan/toggle':
                action = payload['action']
                cmd = '/interface/enable' if action == 'enable' else '/interface/disable'
                api.comm(cmd, {'.id': payload['id']})
                return send_json(self, 200, {'ok': True, 'message': 'vlan_action_done'})
            return send_json(self, 404, {'ok': False, 'error': 'not_found'})
        except KeyError as e:
            return send_json(self, 400, {'ok': False, 'error': 'missing_field', 'field': str(e)})
        except Exception as e:
            return send_json(self, 500, {'ok': False, 'error': 'internal_error', 'detail': str(e), 'trace_tail': traceback.format_exc().splitlines()[-5:]})
        finally:
            if api:
                api.close()

if __name__ == '__main__':
    port = int(os.environ.get('PORT', '9077'))
    print(f'CAS MikroTik Bridge listening on 127.0.0.1:{port}', flush=True)
    HTTPServer(('127.0.0.1', port), Handler).serve_forever()
