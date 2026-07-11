#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Webhook worker — forwards updates to bot webhook URLs
 * Usage: php bin/webhook-worker.php
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Load environment
require BASE_PATH . '/config/env.php';
require BASE_PATH . '/config/database.php';

use App\Core\Database;

// Initialize database connection
$dbConfig = require BASE_PATH . '/config/database.php';
Database::getInstance($dbConfig);

use App\Services\WebhookService;

$worker = new WebhookService();

// Handle graceful shutdown
pcntl_signal(SIGINT, function () use ($worker) {
    echo "\nShutting down...\n";
    $worker->stop();
    exit(0);
});
pcntl_signal(SIGTERM, function () use ($worker) {
    echo "\nShutting down...\n";
    $worker->stop();
    exit(0);
});

echo "Telegram Bot API Webhook Worker\n";
echo "===============================\n\n";

$worker->run();
