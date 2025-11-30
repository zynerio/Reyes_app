<?php
declare(strict_types=1);
$base = __DIR__ . '/app';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$envPath = $base . '/.env';
$autoload = $base . '/vendor/autoload.php';
$bootstrap = $base . '/bootstrap/app.php';
// Detect APP_URL automatically based on current request and app/public path
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$appPath = $dir . '/app/public';
$detectedAppUrl = $scheme . '://' . $host . $appPath;
function preflight(string $base): array {
    $checks = [];
    $phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');
    $checks[] = ['label' => 'PHP ≥ 8.2', 'ok' => $phpOk, 'detail' => PHP_VERSION];
    $pdoOk = extension_loaded('pdo_sqlite');
    $checks[] = ['label' => 'Extensión pdo_sqlite', 'ok' => $pdoOk, 'detail' => $pdoOk ? 'cargada' : 'faltante'];
    $storage = $base . '/storage';
    $storageOk = is_dir($storage) && is_writable($storage);
    $checks[] = ['label' => 'Permisos en storage', 'ok' => $storageOk, 'detail' => $storageOk ? 'escribible' : 'no escribible'];
    $cacheDir = $base . '/bootstrap/cache';
    $cacheOk = is_dir($cacheDir) && is_writable($cacheDir);
    $checks[] = ['label' => 'Permisos en bootstrap/cache', 'ok' => $cacheOk, 'detail' => $cacheOk ? 'escribible' : 'no escribible'];
    $errors = array_values(array_filter($checks, fn($c) => !$c['ok']));
    return ['checks' => $checks, 'hasErrors' => count($errors) > 0];
}
function writeEnv(string $path, array $data): void {
    $lines = [];
    foreach ($data as $k => $v) { $lines[] = $k.'='.$v; }
    file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
}
function post(string $key, string $default = ''): string { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; }
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && post('action') === 'test-conn') {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'message' => 'SQLite no requiere prueba de conexión']);
    exit;
}
if ($method === 'POST' && post('action') === 'install') {
    $appUrl = $detectedAppUrl;
    $createDbWanted = false;
    $firstName = post('FIRST_NAME');
    $firstEmail = post('FIRST_EMAIL');
    $firstUsername = post('FIRST_USERNAME');
    $firstPassword = post('FIRST_PASSWORD');
    $errors = [];
    $urlValid = filter_var($appUrl, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $appUrl);
    if (!$urlValid) { $errors[] = 'APP_URL no válida'; }
    if ($firstName === '' || $firstEmail === '' || $firstPassword === '') { $errors[] = 'Datos del primer usuario incompletos'; }
    if ($firstEmail !== '' && !filter_var($firstEmail, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email del primer usuario no válido'; }
    $sqlitePath = $base . '/database/database.sqlite';
    if (!file_exists($sqlitePath)) {
        try { touch($sqlitePath); } catch (Throwable $e) {}
    }
    $dbOk = file_exists($sqlitePath) && is_writable(dirname($sqlitePath));
    if (!$dbOk) { $errors[] = 'No se pudo preparar la base de datos SQLite'; }
    if (count($errors) > 0) {
        $pf = preflight($base);
        $disabled = $pf['hasErrors'] ? 'disabled' : '';
        $list = '';
        foreach ($pf['checks'] as $c) {
            $color = $c['ok'] ? '#065f46' : '#b91c1c';
            $bg = $c['ok'] ? '#d1fae5' : '#fee2e2';
            $list .= '<li style="display:flex;justify-content:space-between;align-items:center;margin:6px 0;padding:8px 10px;background:'.$bg.';border-radius:8px"><span>'.$c['label'].'</span><span style="color:'.$color.'">'.($c['ok']?'OK':'Fallo').' · '.$c['detail'].'</span></li>';
        }
        $errHtml = '';
        foreach ($errors as $err) { $errHtml .= '<li>'.$err.'</li>'; }
        $banner = '<div style="padding:10px 12px;background:#fee2e2;border:1px solid #ef4444;color:#7f1d1d;border-radius:8px;margin-bottom:12px"><ul style="margin:0; padding-left:18px">'.$errHtml.'</ul></div>';
        $html = <<<HTML
<!doctype html><meta charset="utf-8"><title>Instalador Reyes</title>
<style>@keyframes spin{to{transform:rotate(360deg)}}.spin{display:inline-block;width:16px;height:16px;border:2px solid #93c5fd;border-top-color:#2563eb;border-radius:50%;animation:spin .8s linear infinite;margin-left:8px}</style>
<div style="font-family:system-ui;max-width:720px;margin:32px auto;padding:24px;border:1px solid #e5e7eb;border-radius:12px">
<h1 style="margin:0 0 16px">Instalador</h1>
{$banner}
<ul style="list-style:none;padding:0;margin:0 0 12px">{$list}</ul>
<form method="post" id="installForm">
  <label>URL de la app<br><input type="text" value="{APP_URL}" readonly style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <input type="hidden" name="APP_URL" value="{APP_URL}">
  <label>DB Host (opcional)<br><input name="DB_HOST" placeholder="localhost" value="{DB_HOST}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Port (opcional)<br><input name="DB_PORT" placeholder="3306" value="{DB_PORT}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Nombre (opcional)<br><input name="DB_DATABASE" placeholder="reyes" value="{DB_DATABASE}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Usuario (opcional)<br><input name="DB_USERNAME" placeholder="reyes" value="{DB_USERNAME}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Password (opcional)<br><input name="DB_PASSWORD" type="password" placeholder="" value="{DB_PASSWORD}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label style="display:flex;gap:8px;align-items:center;margin:6px 0 10px"><input type="checkbox" name="CREATE_DB" value="1" {CREATE_DB_CHECKED}> Crear base de datos si no existe (MySQL/MariaDB)</label>
  <fieldset style="margin-top:12px;border:1px solid #e5e7eb;border-radius:8px;padding:12px">
    <legend style="padding:0 6px;color:#374151">Primer usuario (admin)</legend>
    <label>Nombre<br><input name="FIRST_NAME" required placeholder="Nombre" value="{FIRST_NAME}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Email<br><input name="FIRST_EMAIL" type="email" required placeholder="admin@midominio.com" value="{FIRST_EMAIL}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Usuario (opcional)<br><input name="FIRST_USERNAME" placeholder="admin" value="{FIRST_USERNAME}" style="width:100%;max-width:420px;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Contraseña<br>
      <div style="display:flex;gap:8px;align-items:center">
        <input name="FIRST_PASSWORD" type="password" required placeholder="contraseña segura" value="{FIRST_PASSWORD}" style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 14px">
        <button type="button" id="genPwd" style="padding:8px 12px;background:#374151;color:#fff;border-radius:6px;margin:6px 0 14px">Generar</button>
        <button type="button" id="copyPwd" style="padding:8px 12px;background:#2563eb;color:#fff;border-radius:6px;margin:6px 0 14px">Copiar</button>
        <button type="button" id="togglePwd" style="padding:8px 12px;background:#6b7280;color:#fff;border-radius:6px;margin:6px 0 14px">Ver</button>
      </div>
    </label>
  </fieldset>
  <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
    <button type="button" id="testConn" style="padding:8px 12px;background:#f59e0b;color:#111827;border-radius:6px">Probar conexión</button>
    <span id="connMsg" style="color:#6b7280"></span>
  </div>
  <div style="margin-top:12px">
    <button {$disabled} style="padding:8px 12px;background:#2563eb;color:#fff;border-radius:6px">Instalar</button>
  </div>
  <input type="hidden" name="action" value="install">
  <script>
    (function(){
      var form = document.getElementById('installForm');
      var btn = document.getElementById('testConn');
      var msg = document.getElementById('connMsg');
      var email = form && form.querySelector('input[name="FIRST_EMAIL"]');
      var uname = form && form.querySelector('input[name="FIRST_USERNAME"]');
      if (email && uname) {
        var auto = { active: false };
        function updateUsername(){
          var v = (email.value || '').trim();
          var at = v.indexOf('@');
          if (at === -1) return; // Espera a que haya @
          var p = v.substring(0, at);
          if (auto.active || uname.value === '' || uname.dataset.autofill === '1') {
            uname.value = p;
            uname.dataset.autofill = '1';
            auto.active = true;
          }
        }
        email.addEventListener('input', updateUsername);
        uname.addEventListener('input', function(){ uname.dataset.autofill = '0'; auto.active = false; });
      }
      var gen = document.getElementById('genPwd');
      var copy = document.getElementById('copyPwd');
      var toggle = document.getElementById('togglePwd');
      var pwd = form && form.querySelector('input[name="FIRST_PASSWORD"]');
      function genPass(){
        var U='ABCDEFGHJKLMNPQRSTUVWXYZ', L='abcdefghijkmnopqrstuvwxyz', D='23456789', S='!@#$%^&*()-_=+[]{};:,.?';
        function pick(s){ return s[Math.floor(Math.random()*s.length)]; }
        var out=[pick(U),pick(L),pick(D),pick(S)];
        var all=U+L+D+S; for(var i=0;i<16;i++){ out.push(pick(all)); }
        return out.sort(function(){ return Math.random()-0.5; }).join('');
      }
      function notify(text, duration, type){
        var n = document.createElement('div');
        n.textContent = text;
        var bg = type === 'error' ? '#dc2626' : '#2563eb';
        n.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:10px 12px;background:'+bg+';color:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.12);z-index:9999';
        document.body.appendChild(n);
        setTimeout(function(){ n.remove(); }, duration || 4000);
      }
      if (gen && pwd) { gen.addEventListener('click', function(){ pwd.value = genPass(); }); }
      if (copy && pwd) {
        copy.addEventListener('click', function(){
          var txt = (pwd.value || '').trim();
          if (!txt) { notify('La contraseña está vacía', 4000, 'error'); return; }
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(txt)
              .then(function(){ notify('Copiado', 4000); })
              .catch(function(){ notify('Copiado', 4000); });
          } else {
            try { pwd.select(); document.execCommand('copy'); } catch(e) {}
            notify('Copiado', 4000);
          }
        });
      }
      if (toggle && pwd) {
        toggle.addEventListener('click', function(){
          var t = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd.setAttribute('type', t);
          toggle.textContent = t === 'password' ? 'Ver' : 'Ocultar';
        });
      }
      if (btn) {
        btn.addEventListener('click', function(){
          msg.textContent = 'Probando conexión...';
          var fd = new FormData(form);
          fd.set('action','test-conn');
          fetch('installer.php', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){ msg.textContent = d.ok ? 'Conexión correcta' : ('Error: ' + d.message); })
            .catch(function(e){ msg.textContent = e && e.message ? e.message : 'Error desconocido'; });
        });
      }
    })();
  </script>
</form>
</div>
HTML;
        echo strtr($html, [
            '{APP_URL}' => htmlspecialchars($appUrl),
            '{DB_HOST}' => '',
            '{DB_PORT}' => '',
            '{DB_DATABASE}' => '',
            '{DB_USERNAME}' => '',
            '{DB_PASSWORD}' => '',
            '{FIRST_NAME}' => htmlspecialchars($firstName),
            '{FIRST_EMAIL}' => htmlspecialchars($firstEmail),
            '{FIRST_USERNAME}' => htmlspecialchars($firstUsername),
            '{FIRST_PASSWORD}' => htmlspecialchars($firstPassword),
            '{CREATE_DB_CHECKED}' => '',
        ]);
        exit;
    }
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $env = [
        'APP_NAME' => 'Reyes',
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'APP_URL' => $appUrl,
        'APP_KEY' => $appKey,
        'LOG_CHANNEL' => 'stack',
        'LOG_LEVEL' => 'info',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => $sqlitePath,
        'BROADCAST_DRIVER' => 'log',
        'CACHE_DRIVER' => 'file',
        'CACHE_STORE' => 'file',
        'FILESYSTEM_DISK' => 'public',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'file',
        'SESSION_LIFETIME' => '120',
    ];
    writeEnv($envPath, $env);
    require $autoload;
    $app = require $bootstrap;
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $steps = [];
    $codeKey = Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
    $outKey = Illuminate\Support\Facades\Artisan::output();
    $okKey = ($codeKey === 0);
    $steps[] = ['label' => 'Clave generada', 'ok' => $okKey, 'out' => $outKey];
    $codeCfg = Illuminate\Support\Facades\Artisan::call('config:cache');
    $outCfg = Illuminate\Support\Facades\Artisan::output();
    $okCfg = ($codeCfg === 0);
    $steps[] = ['label' => 'Config cacheado', 'ok' => $okCfg, 'out' => $outCfg];
    $codeRt = Illuminate\Support\Facades\Artisan::call('route:clear');
    $outRt = Illuminate\Support\Facades\Artisan::output();
    $okRt = ($codeRt === 0);
    $steps[] = ['label' => 'Rutas limpiadas', 'ok' => $okRt, 'out' => $outRt];
    $allApplied = false;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
            $files = glob($base . '/database/migrations/*.php') ?: [];
            $expected = array_map(fn($f) => basename($f, '.php'), $files);
            $applied = \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all();
            $missing = array_diff($expected, $applied);
            $allApplied = count($expected) > 0 && count($missing) === 0;
        }
    } catch (\Throwable $e) { $allApplied = false; }
    if ($allApplied) {
        $okMig = true;
        $steps[] = ['label' => 'Migraciones omitidas', 'ok' => true, 'out' => 'Todas las migraciones ya están aplicadas'];
    } else {
        $labelMig = 'Migraciones ejecutadas';
        $okMig = false;
        $outMig = '';
        if ($createDbWanted) {
            $codeMig = Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
            $outMig = Illuminate\Support\Facades\Artisan::output();
            $okMig = ($codeMig === 0);
            $labelMig = 'Migraciones (fresh) ejecutadas';
        } else {
            $codeMig = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $outMig = Illuminate\Support\Facades\Artisan::output();
            $okMig = ($codeMig === 0);
            if (!$okMig && (stripos($outMig, 'already exists') !== false || stripos($outMig, 'Base table or view already exists') !== false)) {
                $codeMig2 = Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
                $outMig2 = Illuminate\Support\Facades\Artisan::output();
                $okMig = ($codeMig2 === 0);
                $outMig = "Reintento con migrate:fresh\n\n".$outMig2;
                $labelMig = 'Migraciones (fresh) ejecutadas tras conflicto';
            }
        }
        $steps[] = ['label' => $labelMig, 'ok' => $okMig, 'out' => $outMig];
    }
    try {
        $conn = Illuminate\Support\Facades\DB::connection();
        $driver = $conn->getDriverName();
        $names = [];
        if ($driver === 'sqlite') {
            $rows = Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            $names = array_values(array_filter(array_map(function($r){ return isset($r->name) ? $r->name : ''; }, $rows)));
        } else {
            $rows = Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $names = array_values(array_filter(array_map(function($r){ return (array_values((array)$r)[0]) ?? ''; }, $rows)));
        }
        $summary = 'Total: ' . count($names);
        if (count($names) > 0) { $summary .= "\n" . implode(', ', $names); }
        $steps[] = ['label' => 'Tablas detectadas', 'ok' => true, 'out' => $summary];
    } catch (\Throwable $e) {
        $steps[] = ['label' => 'Tablas detectadas', 'ok' => false, 'out' => 'Error listando tablas: '.$e->getMessage()];
    }
    $ok = ($okKey && $okCfg && $okRt && $okMig);
    $setupOk = ($okKey && $okCfg && $okMig);
    $msg = $ok ? 'Instalación completada' : 'Instalación con avisos';
    $userMsg = '';
    if ($setupOk) {
        try {
            $created = false;
            $newUser = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                $exists = \App\Models\User::query()->count() > 0;
                if (! $exists) {
                    $uname = $firstUsername !== '' ? $firstUsername : (strpos($firstEmail, '@') !== false ? substr($firstEmail, 0, strpos($firstEmail, '@')) : 'admin');
                    $newUser = \App\Models\User::create([
                        'name' => $firstName,
                        'email' => $firstEmail,
                        'username' => $uname,
                        'password' => $firstPassword,
                        'role' => 'admin',
                    ]);
                    $created = true;
                }
            }
            $userMsg = $created ? 'Usuario admin creado: '.htmlspecialchars($firstEmail) : 'Usuario no creado (ya existía alguno)';
            if ($created && $newUser) {
                \Illuminate\Support\Facades\Session::start();
                \Illuminate\Support\Facades\Auth::login($newUser);
                $sid = \Illuminate\Support\Facades\Session::getId();
                $cookieName = \Illuminate\Support\Facades\Config::get('session.cookie');
                $cookiePath = \Illuminate\Support\Facades\Config::get('session.path', '/');
                $cookieDomain = \Illuminate\Support\Facades\Config::get('session.domain');
                $cookieSecure = (bool) \Illuminate\Support\Facades\Config::get('session.secure', false);
                $cookieHttpOnly = (bool) \Illuminate\Support\Facades\Config::get('session.http_only', true);
                $cookieSameSite = \Illuminate\Support\Facades\Config::get('session.same_site', 'lax');
                $ttl = (int) \Illuminate\Support\Facades\Config::get('session.lifetime', 120) * 60;
                setcookie($cookieName, $sid, [
                    'expires' => time() + $ttl,
                    'path' => $cookiePath,
                    'domain' => $cookieDomain ?? '',
                    'secure' => $cookieSecure,
                    'httponly' => $cookieHttpOnly,
                    'samesite' => $cookieSameSite,
                ]);
            }
        } catch (\Throwable $e) {
            $userMsg = 'Error creando usuario: '.htmlspecialchars($e->getMessage());
        }
    } else {
        $missing = [];
        if (!$okKey) { $missing[] = 'clave'; }
        if (!$okCfg) { $missing[] = 'config'; }
        if (!$okMig) { $missing[] = 'migraciones'; }
        $userMsg = 'Usuario no creado: requisitos previos incompletos'.(count($missing)?' ('.implode(', ', $missing).')':'');
    }
    $list = '';
    foreach ($steps as $s) {
        $color = $s['ok'] ? '#10b981' : '#ef4444';
        $bg = $s['ok'] ? '#ecfdf5' : '#fee2e2';
        $icon = $s['ok'] ? '✓' : '✗';
        $details = '';
        if (isset($s['out']) && $s['out'] !== '') {
            $details = '<details style="margin-top:6px"><summary style="cursor:pointer;color:#374151">Ver detalles</summary><pre style="white-space:pre-wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px">'.htmlspecialchars($s['out']).'</pre></details>';
        }
        $list .= '<li style="margin:6px 0;padding:8px 10px;background:'.$bg.';border:1px solid '.$color.';border-radius:8px"><div style="display:flex;justify-content:space-between;align-items:center"><span>'.$s['label'].'</span><span style="color:'.$color.'">'.$icon.'</span></div>'.$details.'</li>';
    }
    $redirect = rtrim($appUrl, '/').'/';
    $activeText = '';
    try {
        $u = \Illuminate\Support\Facades\Auth::user();
        if ($u) { $activeText = 'Cuenta activa: '.htmlspecialchars($u->name).' ('.htmlspecialchars($u->email).')'; }
    } catch (\Throwable $e) {}
    $html = <<<HTML
<!doctype html><meta charset="utf-8"><title>Instalación</title>
<div style="font-family:system-ui;padding:24px">
  <h1>{$msg}</h1>
  <ul style="list-style:none;padding:0;margin:12px 0">{$list}</ul>
  {USER_MSG}
  {ACTIVE_TEXT}
  <p>APP_URL: {APP_URL}</p>
  <p><a href="{REDIRECT}" style="display:inline-block;padding:8px 12px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none">Ir al inicio</a><span style="color:#6b7280;margin-left:8px">Se redirige en 3 segundos…</span></p>
  <script>setTimeout(function(){window.location.href={REDIRECT_JSON};},3000);</script>
</div>
HTML;
    echo strtr($html, [
        '{USER_MSG}' => ($userMsg!==''?'<p>'.$userMsg.'</p>':''),
        '{ACTIVE_TEXT}' => ($activeText!==''?'<p>'.$activeText.'</p>':''),
        '{APP_URL}' => htmlspecialchars($appUrl),
        '{REDIRECT}' => htmlspecialchars($redirect),
        '{REDIRECT_JSON}' => json_encode($redirect),
    ]);
    exit;
}
$pf = preflight($base);
$disabled = $pf['hasErrors'] ? 'disabled' : '';
$list = '';
foreach ($pf['checks'] as $c) {
    $color = $c['ok'] ? '#065f46' : '#b91c1c';
    $bg = $c['ok'] ? '#d1fae5' : '#fee2e2';
    $list .= '<li style="display:flex;justify-content:space-between;align-items:center;margin:6px 0;padding:8px 10px;background:'.$bg.';border-radius:8px"><span>'.$c['label'].'</span><span style="color:'.$color.'">'.($c['ok']?'OK':'Fallo').' · '.$c['detail'].'</span></li>';
}
$banner = $pf['hasErrors'] ? '<div style="padding:10px 12px;background:#fef3c7;border:1px solid #f59e0b;color:#92400e;border-radius:8px;margin-bottom:12px">Corrige los requisitos señalados antes de instalar.</div>' : '';
// Initial form view (GET)
$htmlStart = <<<HTML
<!doctype html><meta charset="utf-8"><title>Instalador Reyes</title>
<style>@keyframes spin{to{transform:rotate(360deg)}}.spin{display:inline-block;width:16px;height:16px;border:2px solid #93c5fd;border-top-color:#2563eb;border-radius:50%;animation:spin .8s linear infinite;margin-left:8px}</style>
<div style="font-family:system-ui;max-width:720px;margin:32px auto;padding:24px;border:1px solid #e5e7eb;border-radius:12px">
<h1 style="margin:0 0 16px">Instalador</h1>
{$banner}
<ul style="list-style:none;padding:0;margin:0 0 12px">{$list}</ul>
<form method="post" id="installForm">
  <label>URL de la app<br><input type="text" value="{APP_URL}" readonly style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <input type="hidden" name="APP_URL" value="{APP_URL}">
  <label>DB Host (opcional)<br><input name="DB_HOST" placeholder="localhost" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Port (opcional)<br><input name="DB_PORT" placeholder="3306" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Nombre (opcional)<br><input name="DB_DATABASE" placeholder="reyes" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Usuario (opcional)<br><input name="DB_USERNAME" placeholder="reyes" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label>DB Password (opcional)<br><input name="DB_PASSWORD" type="password" placeholder="" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
  <label style="display:flex;gap:8px;align-items:center;margin:6px 0 10px"><input type="checkbox" name="CREATE_DB" value="1"> Crear base de datos si no existe (MySQL/MariaDB)</label>
  <fieldset style="margin-top:12px;border:1px solid #e5e7eb;border-radius:8px;padding:12px">
    <legend style="padding:0 6px;color:#374151">Primer usuario (admin)</legend>
    <label>Nombre<br><input name="FIRST_NAME" required placeholder="Nombre" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Email<br><input name="FIRST_EMAIL" type="email" required placeholder="admin@midominio.com" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Usuario (opcional)<br><input name="FIRST_USERNAME" placeholder="admin" style="width:100%;max-width:100%;box-sizing:border-box;display:block;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px auto 14px"></label>
    <label>Contraseña<br>
      <div style="display:flex;gap:8px;align-items:center">
        <input name="FIRST_PASSWORD" type="password" required placeholder="contraseña segura" style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:6px 0 14px">
        <button type="button" id="genPwd" style="padding:8px 12px;background:#374151;color:#fff;border-radius:6px;margin:6px 0 14px">Generar</button>
        <button type="button" id="copyPwd" style="padding:8px 12px;background:#2563eb;color:#fff;border-radius:6px;margin:6px 0 14px">Copiar</button>
        <button type="button" id="togglePwd" style="padding:8px 12px;background:#6b7280;color:#fff;border-radius:6px;margin:6px 0 14px">Ver</button>
      </div>
    </label>
  </fieldset>
  <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
    <button type="button" id="testConn" style="padding:8px 12px;background:#f59e0b;color:#111827;border-radius:6px">Probar conexión</button>
    <span id="connMsg" style="color:#6b7280"></span>
  </div>
  <div style="margin-top:12px">
    <button {$disabled} style="padding:8px 12px;background:#2563eb;color:#fff;border-radius:6px">Instalar</button>
  </div>
  <input type="hidden" name="action" value="install">
  <script>
    (function(){
      var form = document.getElementById('installForm');
      var btn = document.getElementById('testConn');
      var msg = document.getElementById('connMsg');
      var email = form && form.querySelector('input[name="FIRST_EMAIL"]');
      var uname = form && form.querySelector('input[name="FIRST_USERNAME"]');
      if (email && uname) {
        var auto = { active: false };
        function updateUsername(){
          var v = (email.value || '').trim();
          var at = v.indexOf('@');
          if (at === -1) return;
          var p = v.substring(0, at);
          if (auto.active || uname.value === '' || uname.dataset.autofill === '1') {
            uname.value = p;
            uname.dataset.autofill = '1';
            auto.active = true;
          }
        }
        email.addEventListener('input', updateUsername);
        uname.addEventListener('input', function(){ uname.dataset.autofill = '0'; auto.active = false; });
      }
      var gen = document.getElementById('genPwd');
      var copy = document.getElementById('copyPwd');
      var toggle = document.getElementById('togglePwd');
      var pwd = form && form.querySelector('input[name="FIRST_PASSWORD"]');
      function genPass(){
        var U='ABCDEFGHJKLMNPQRSTUVWXYZ', L='abcdefghijkmnopqrstuvwxyz', D='23456789', S='!@#$%^&*()-_=+[]{};:,.?';
        function pick(s){ return s[Math.floor(Math.random()*s.length)]; }
        var out=[pick(U),pick(L),pick(D),pick(S)];
        var all=U+L+D+S; for(var i=0;i<16;i++){ out.push(pick(all)); }
        return out.sort(function(){ return Math.random()-0.5; }).join('');
      }
      function notify(text, duration, type){
        var n = document.createElement('div');
        n.textContent = text;
        var bg = type === 'error' ? '#dc2626' : '#2563eb';
        n.style.cssText = 'position:fixed;bottom:20px;right:20px;padding:10px 12px;background:'+bg+';color:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.12);z-index:9999';
        document.body.appendChild(n);
        setTimeout(function(){ n.remove(); }, duration || 4000);
      }
      if (gen && pwd) { gen.addEventListener('click', function(){ pwd.value = genPass(); }); }
      if (copy && pwd) {
        copy.addEventListener('click', function(){
          var txt = (pwd.value || '').trim();
          if (!txt) { notify('La contraseña está vacía', 4000, 'error'); return; }
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(txt)
              .then(function(){ notify('Copiado', 4000); })
              .catch(function(){ notify('Copiado', 4000); });
          } else {
            try { pwd.select(); document.execCommand('copy'); } catch(e) {}
            notify('Copiado', 4000);
          }
        });
      }
      if (toggle && pwd) {
        toggle.addEventListener('click', function(){
          var t = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
          pwd.setAttribute('type', t);
          toggle.textContent = t === 'password' ? 'Ver' : 'Ocultar';
        });
      }
      if (btn) {
        btn.addEventListener('click', function(){
          msg.textContent = 'Probando conexión...';
          var fd = new FormData(form);
          fd.set('action','test-conn');
          fetch('installer.php', { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(d){ msg.textContent = d.ok ? 'Conexión correcta' : ('Error: ' + d.message); })
            .catch(function(e){ msg.textContent = e && e.message ? e.message : 'Error desconocido'; });
        });
      }
    })();
  </script>
  </form>
  </div>
HTML;
echo strtr($htmlStart, [ '{APP_URL}' => htmlspecialchars($detectedAppUrl) ]);
