<?php
/**
 * One-time emergency cache clear — deletes cached files directly (no artisan).
 * DELETE THIS FILE after use.
 * Access: https://production.epochmines.co.zw/clearcache.php?token=clear-mymine-2026
 */

define('TOKEN', 'clear-mymine-2026');
define('APP_DIR', dirname(__DIR__));

header('Content-Type: text/plain');

if (($_GET['token'] ?? '') !== TOKEN) {
    http_response_code(403);
    exit("Forbidden.\n");
}

// ── 1. git pull ───────────────────────────────────────────────
echo "=== git pull ===\n";
$phpCandidates = [
    '/opt/cpanel/ea-php82/root/usr/bin/php',
    '/usr/local/bin/php82',
    '/usr/local/bin/php8.2',
    '/usr/local/bin/php',
    '/usr/bin/php82',
    '/usr/bin/php8.2',
];
$phpBin = null;
foreach ($phpCandidates as $c) {
    if (file_exists($c) && is_executable($c)) {
        $phpBin = $c;
        break;
    }
}
echo "PHP CLI found: " . ($phpBin ?? 'not found (will skip artisan)') . "\n";

$gitOut = shell_exec('cd ' . APP_DIR . ' && git pull origin main 2>&1');
echo $gitOut . "\n";

// ── 2. Delete bootstrap/cache files directly ──────────────────
echo "=== Clearing bootstrap/cache ===\n";
$cacheDir = APP_DIR . '/bootstrap/cache';
foreach (glob($cacheDir . '/*.php') as $file) {
    if (unlink($file)) {
        echo "Deleted: " . basename($file) . "\n";
    } else {
        echo "Failed to delete: " . basename($file) . "\n";
    }
}

// ── 3. Delete compiled views ──────────────────────────────────
echo "\n=== Clearing compiled views ===\n";
$viewsDir = APP_DIR . '/storage/framework/views';
$deleted = 0;
foreach (glob($viewsDir . '/*.php') as $file) {
    if (unlink($file)) $deleted++;
}
echo "Deleted {$deleted} compiled view file(s).\n";

// ── 4. Re-run artisan commands with correct PHP binary ────────
if ($phpBin) {
    $commands = [
        "{$phpBin} " . APP_DIR . "/artisan config:cache",
        "{$phpBin} " . APP_DIR . "/artisan route:cache",
        "{$phpBin} " . APP_DIR . "/artisan view:cache",
        "{$phpBin} " . APP_DIR . "/artisan migrate --force",
    ];
    foreach ($commands as $cmd) {
        echo "\n=== $ {$cmd} ===\n";
        echo shell_exec($cmd . ' 2>&1');
    }
} else {
    echo "\nSkipped artisan (no PHP CLI found) — cache files deleted directly, app will rebuild on next request.\n";
}

echo "\n\nDone. DELETE this file now: public/clearcache.php\n";
