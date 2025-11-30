<?php
declare(strict_types=1);
$base = __DIR__ . '/app';
$envPath = $base . '/.env';
function readEnv(string $path): array {
    $out = [];
    if (!file_exists($path)) return $out;
    foreach (file($path) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $eq = strpos($line, '=');
        if ($eq !== false) { $k = substr($line, 0, $eq); $v = substr($line, $eq + 1); $out[$k] = $v; }
    }
    return $out;
}
$env = readEnv($envPath);
$installed = isset($env['APP_KEY']) && $env['APP_KEY'] !== '';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$appPath = $dir . '/app/public';
if ($installed) {
    $target = rtrim($scheme . '://' . $host . $appPath, '/') . '/login';
    header('Location: ' . $target);
    echo '<!doctype html><meta charset="utf-8"><title>Redirigiendo</title><meta http-equiv="refresh" content="0;url='.htmlspecialchars($target).'">';
    exit;
}
require __DIR__ . '/installer.php';
