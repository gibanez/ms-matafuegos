<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Category\Infrastructure\Http\CategoryController;
use Slim\Routing\RouteCollectorProxy;

$group->get('/public/categories', [CategoryController::class, 'listPublic']);

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/categories', [CategoryController::class, 'list']);
    $writeGroup->get('/categories/{id}', [CategoryController::class, 'show']);
    $writeGroup->post('/categories', [CategoryController::class, 'create']);
    $writeGroup->put('/categories/{id}', [CategoryController::class, 'update']);
    $writeGroup->delete('/categories/{id}', [CategoryController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
