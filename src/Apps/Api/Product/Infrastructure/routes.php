<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Product\Infrastructure\Http\ProductController;
use Slim\Routing\RouteCollectorProxy;

$group->get('/public/products', [ProductController::class, 'publicList']);
$group->get('/public/products/{slug}', [ProductController::class, 'publicShow']);

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/products/low-stock', [ProductController::class, 'lowStock']);
    $writeGroup->get('/products', [ProductController::class, 'list']);
    $writeGroup->get('/products/{id}', [ProductController::class, 'show']);
    $writeGroup->post('/products', [ProductController::class, 'create']);
    $writeGroup->put('/products/{id}', [ProductController::class, 'update']);
    $writeGroup->delete('/products/{id}', [ProductController::class, 'delete']);
    $writeGroup->patch('/products/{id}/stock', [ProductController::class, 'adjustStock']);
})->add(JwtAuthMiddleware::class);
