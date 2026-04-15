<?php
/**
 * Emergency fix for 500 error caused by stale route cache.
 * Deletes bootstrap/cache + compiled views directly (no artisan needed).
 * DELETE THIS FILE after use.
 * Access: https://production.epochmines.co.zw/fix500.php?token=clear-mymine-2026
 */

define('TOKEN', 'clear-mymine-2026');
define('APP_DIR', dirname(__DIR__));

header('Content-Type: text/plain');

if (($_GET['token'] ?? '') !== TOKEN) {
    http_response_code(403);
    exit("Forbidden.\n");
}

// Reset opcache for this worker process
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "opcache_reset() called.\n";
} else {
    echo "opcache not available (OK).\n";
}

// ── Delete bootstrap/cache PHP files (route, config, events cache) ────
echo "\n=== Clearing bootstrap/cache ===\n";
$deleted = 0;
foreach (glob(APP_DIR . '/bootstrap/cache/*.php') as $f) {
    if (unlink($f)) {
        echo "Deleted: " . basename($f) . "\n";
        $deleted++;
    } else {
        echo "FAILED to delete: " . basename($f) . "\n";
    }
}
if ($deleted === 0) echo "(nothing found — already empty)\n";

// ── Delete compiled Blade views ───────────────────────────────────────
echo "\n=== Clearing compiled views ===\n";
$deleted = 0;
foreach (glob(APP_DIR . '/storage/framework/views/*.php') as $f) {
    if (unlink($f)) $deleted++;
}
echo "Deleted {$deleted} compiled view file(s).\n";

// ── Verify route cache is gone ────────────────────────────────────────
echo "\n=== Remaining bootstrap/cache files ===\n";
$remaining = glob(APP_DIR . '/bootstrap/cache/*.php');
if (empty($remaining)) {
    echo "CLEAN — no PHP files in bootstrap/cache. Routes will load from web.php.\n";
} else {
    foreach ($remaining as $f) echo "Still present: " . basename($f) . "\n";
}

echo "\n\nDone. Test the settings page now, then DELETE this file.\n";
