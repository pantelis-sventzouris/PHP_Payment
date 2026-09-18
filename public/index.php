<?php

declare(strict_types=1);

use App\Http\JsonResponse;

$container = require dirname(__DIR__) . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($method === 'POST' && $path === '/charges') {
    $container['chargeController']->store();
}

JsonResponse::send(['error' => 'Not found'], 404);
