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

// Fix for missing cacert.pem in local XAMPP
if (file_exists(__DIR__.'/../cacert.pem')) {
    ini_set('openssl.cafile', __DIR__.'/../cacert.pem');
    ini_set('curl.cainfo', __DIR__.'/../cacert.pem');
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
