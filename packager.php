<?php
declare(strict_types=1);
$src = realpath(__DIR__ . '/../app');
$dst = __DIR__ . '/app';
function rmrf(string $path): void { if (!file_exists($path)) return; if (is_file($path) || is_link($path)) { @unlink($path); return; } $it = new FilesystemIterator($path); foreach ($it as $f) { rmrf($f->getPathname()); } @rmdir($path); }
function ensureDir(string $path): void { if (!is_dir($path)) mkdir($path, 0775, true); }
function shouldSkip(string $path): bool {
    $skipDirs = ['.git','.idea','.vscode','.fleet','node_modules','tests','storage'.DIRECTORY_SEPARATOR.'pail','public'.DIRECTORY_SEPARATOR.'hot'];
    foreach ($skipDirs as $d) { if (str_contains($path, DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR)) return true; }
    $skipFiles = ['.env','.env.backup','.phpunit.result.cache'];
    foreach ($skipFiles as $f) { if (str_ends_with($path, DIRECTORY_SEPARATOR.$f)) return true; }
    return false;
}
function copyDir(string $src, string $dst): void {
    ensureDir($dst);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $path = $item->getPathname();
        if (shouldSkip($path)) continue;
        $rel = substr($path, strlen($src));
        $target = $dst . $rel;
        if ($item->isFile()) { ensureDir(dirname($target)); copy($path, $target); } else { ensureDir($target); }
    }
}
rmrf($dst);
copyDir($src, $dst);
echo "OK\n";
