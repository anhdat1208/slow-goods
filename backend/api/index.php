<?php

/**
 * Vercel serverless entrypoint for the Laravel API.
 * Deploy the `backend` directory as its own Vercel project.
 *
 * php -S / vercel-php serve this file as /api/index.php. Laravel would then
 * treat "/api" as the app base path and miss prefixed routes like /api/products.
 */
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../public/index.php';
