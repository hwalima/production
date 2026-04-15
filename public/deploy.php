<?php
/**
 * Webhook deploy script — called by GitHub on every push to main.
 * URL: https://production.epochmines.co.zw/deploy.php
 *
 * Set the same secret in GitHub:
 *   Repo → Settings → Webhooks → Secret
 */

// ── Config ───────────────────────────────────────────────────
define('DEPLOY_SECRET', getenv('DEPLOY_SECRET') ?: 'epochmines-deploy-2026');
define('APP_DIR',       '/home/trukumb2/public_html/mymine');
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

// 4. Run deploy commands
$php = findPhpBin();
$dir = APP_DIR;

$commands = [
    "cd {$dir} && git pull origin " . BRANCH,
    "{$php} {$dir}/artisan config:clear",
    "{$php} {$dir}/artisan view:clear",
    "{$php} {$dir}/artisan route:clear",
    "{$php} {$dir}/artisan config:cache",
    "{$php} {$dir}/artisan route:cache",
    "{$php} {$dir}/artisan view:cache",
    "{$php} {$dir}/artisan migrate --force",
];

$log   = "\n[" . date('Y-m-d H:i:s') . "] Deploy triggered by push to " . BRANCH . "\n";
$output = '';

foreach ($commands as $cmd) {
    $result = shell_exec($cmd . ' 2>&1');
    $line   = "$ {$cmd}\n{$result}\n";
    $output .= $line;
    $log    .= $line;
}

// 5. Write to log file
file_put_contents(LOG_FILE, $log, FILE_APPEND);

http_response_code(200);
echo "Deploy complete.\n\n" . $output;
