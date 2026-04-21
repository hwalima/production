<?php
/**
 * OPcache reset endpoint — called by deploy.php after git pull.
 * Protected by the same deploy secret.
 */
// Read secret from .env
function _readDeploySecret(): string {
    $env = dirname(__DIR__) . '/.env';
    if (file_exists($env)) {
        foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos($line, 'DEPLOY_SECRET=') === 0) {
                return trim(substr($line, 14), " \t'\"");
            }
        }
    }
    return getenv('DEPLOY_SECRET') ?: 'mymine-deploy-2026';
}
define('DEPLOY_SECRET', _readDeploySecret());

$token = $_GET['token'] ?? '';
if (!hash_equals(DEPLOY_SECRET, $token)) {
    http_response_code(403);
    exit("Forbidden\n");
}

header('Content-Type: text/plain');

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "opcache_reset() called successfully.\n";
} else {
    echo "OPcache not available.\n";
}

// Also report current file hash of key view file so we can verify
$f = dirname(__DIR__) . '/resources/views/pdf/production.blade.php';
if (file_exists($f)) {
    echo "pdf/production.blade.php md5: " . md5_file($f) . "\n";
    echo "pdf/production.blade.php first line: " . fgets(fopen($f, 'r')) . "\n";
    $lines = file($f);
    echo "pdf/production.blade.php line 2: " . ($lines[1] ?? '') . "\n";
    echo "pdf/production.blade.php line 3: " . ($lines[2] ?? '') . "\n";
}

$f2 = dirname(__DIR__) . '/resources/views/pdf/layout.blade.php';
if (file_exists($f2)) {
    echo "pdf/layout.blade.php md5: " . md5_file($f2) . "\n";
    $lines2 = file($f2);
    // Check for "Exported by" in layout
    $found = false;
    foreach ($lines2 as $i => $line) {
        if (stripos($line, 'Exported by') !== false) {
            echo "FOUND 'Exported by' at line " . ($i+1) . ": " . trim($line) . "\n";
            $found = true;
        }
    }
    if (!$found) echo "pdf/layout.blade.php: no 'Exported by' text found (correct).\n";
}
