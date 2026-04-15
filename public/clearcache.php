<?php
/**
 * One-time emergency cache clear.
 * DELETE THIS FILE after use.
 * Access: https://production.epochmines.co.zw/clearcache.php?token=clear-mymine-2026
 */

define('TOKEN', 'clear-mymine-2026');
define('APP_DIR', dirname(__DIR__));
define('PHP_BIN', '/usr/bin/php');

header('Content-Type: text/plain');

if (($_GET['token'] ?? '') !== TOKEN) {
    http_response_code(403);
    exit("Forbidden.\n");
}

$commands = [
    "cd " . APP_DIR . " && git pull origin main",
    PHP_BIN . " " . APP_DIR . "/artisan config:clear",
    PHP_BIN . " " . APP_DIR . "/artisan view:clear",
    PHP_BIN . " " . APP_DIR . "/artisan route:clear",
    PHP_BIN . " " . APP_DIR . "/artisan config:cache",
    PHP_BIN . " " . APP_DIR . "/artisan route:cache",
    PHP_BIN . " " . APP_DIR . "/artisan view:cache",
    PHP_BIN . " " . APP_DIR . "/artisan migrate --force",
];

echo "Running deploy commands...\n\n";

foreach ($commands as $cmd) {
    echo "$ {$cmd}\n";
    echo shell_exec($cmd . ' 2>&1');
    echo "\n";
}

echo "\nDone. DELETE this file now: public/clearcache.php\n";
