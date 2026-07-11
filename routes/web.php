<?php
declare(strict_types=1);

use App\Core\Router;

/** @var Router $router */

// Serve uploaded files (internal format)
$router->get('/file/{file_id}', 'App\Controllers\MediaFileController@serve');

// Telegram-style file serving: /file/bot{token}/{file_path}
// Matches Telegram Bot API format exactly
$router->get('/file/bot{token}/{file_path:.*}', 'App\Controllers\MediaFileController@serveBotFile');

// Telegram webhook endpoint (receives bot updates)
$router->post('/webhook/{token}', 'App\Controllers\BotApi\WebhookController@handle');

// Static file serving (assets, icons, etc.)
$router->get('/assets/{path:.*}', 'App\Controllers\StaticController@serve');
