<?php
/**
 * Webhook deploy script — called by GitHub on every push to main.
 * Works on any server — reads config from the app's .env file.
 *
 * Add to each server's .env:
 *   DEPLOY_SECRET=your-secret-here
 *
 * Add one GitHub webhook per server:
 *   https://yourserver.com/deploy.php  (same secret)
 */

// ── Bootstrap: read .env from one level up (laravel root) ────
function readDotEnv(string $path): array {
    if (!file_exists($path)) return [];
    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $vars[trim($key)] = trim($val, " \t\n\r\0\x0B'\"");
    }
    return $vars;
}

$appDir  = dirname(__DIR__);          // public/../  = laravel root
$envVars = readDotEnv($appDir . '/.env');

// ── Config ───────────────────────────────────────────────────
define('DEPLOY_SECRET', $envVars['DEPLOY_SECRET'] ?? getenv('DEPLOY_SECRET') ?: 'mymine-deploy-2026');
define('APP_DIR',       $appDir);
define('LOG_FILE',      APP_DIR . '/storage/logs/deploy.log');
define('BRANCH',        'main');

// Detect the correct PHP CLI binary (cPanel EasyApache path)
function findPhpBin(): string {
    $candidates = [
        '/opt/cpanel/ea-php82/root/usr/bin/php',
        '/usr/local/bin/php82',
        '/usr/local/bin/php8.2',
        '/usr/local/bin/php',
        '/usr/bin/php82',
        '/usr/bin/php8.2',
        '/usr/bin/php',
    ];
    foreach ($candidates as $c) {
        if (file_exists($c) && is_executable($c)) return $c;
    }
    return 'php'; // fallback
}

// Detect the correct Composer binary, downloading it if necessary
function findComposerBin(): string {
    $php = findPhpBin();
    // Detect home dir dynamically so this works on any server account
    $home = getenv('HOME') ?: (function_exists('posix_getpwuid') ? (posix_getpwuid(posix_getuid())['dir'] ?? '/tmp') : '/tmp');
    $candidates = [
        '/usr/local/bin/composer',
        '/usr/bin/composer',
        '/opt/cpanel/ea-php82/root/usr/bin/composer',
        '/usr/local/cpanel/3rdparty/bin/composer',
        $home . '/bin/composer',
    ];
    foreach ($candidates as $c) {
        if (file_exists($c) && is_executable($c)) return $c;
    }
    // Check for .phar files
    $phars = [
        '/usr/local/bin/composer.phar',
        $home . '/composer.phar',
        APP_DIR . '/composer.phar',
    ];
    foreach ($phars as $p) {
        if (file_exists($p) && is_readable($p)) return "{$php} {$p}";
    }
    // Download to /tmp as last resort
    $phar = '/tmp/deploy-composer.phar';
    if (!file_exists($phar)) {
        @copy('https://getcomposer.org/composer-stable.phar', $phar);
    }
    if (file_exists($phar)) return "{$php} {$phar}";
    return 'composer'; // final fallback
}
// ─────────────────────────────────────────────────────────────

header('Content-Type: text/plain');

// 1. Only accept POST from GitHub
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.\n");
}

// 2. Verify GitHub signature
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected  = 'sha256=' . hash_hmac('sha256', $payload, DEPLOY_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    exit("Signature mismatch.\n");
}

// 3. Only deploy on pushes to the configured branch
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . BRANCH) {
    http_response_code(200);
    exit("Push to '{$ref}' ignored (not " . BRANCH . ").\n");
}

// 4. Write a deploy-pending flag — the cron job picks this up and runs the actual deploy
//    (shell_exec / exec are disabled in web PHP on this host)
$flagFile = APP_DIR . '/storage/deploy-pending';
$meta     = json_encode([
    'triggered_at' => date('Y-m-d H:i:s'),
    'ref'          => $ref,
    'commit'       => $data['after'] ?? 'unknown',
    'pusher'       => $data['pusher']['name'] ?? 'unknown',
]);

if (file_put_contents($flagFile, $meta) === false) {
    http_response_code(500);
    exit("Could not write deploy flag to {$flagFile}\n");
}

http_response_code(200);
echo "Deploy queued. Cron job will execute within 1 minute.\n{$meta}\n";
