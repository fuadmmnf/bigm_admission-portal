<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bind the public path to the directory containing this index.php.
// This is required on shared/cPanel hosting where the project lives outside
// the web root and only the public/ folder is the document root, so that
// commands like `storage:link` create symlinks in the correct location.
$app->bind('path.public', fn() => __DIR__);

$app->handleRequest(Request::capture());
