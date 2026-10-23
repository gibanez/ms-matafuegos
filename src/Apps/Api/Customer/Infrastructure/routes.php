<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Customer\Infrastructure\Http\CustomerController;
use Slim\Routing\RouteCollectorProxy;

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/customers', [CustomerController::class, 'list']);
    $writeGroup->get('/customers/{id}', [CustomerController::class, 'show']);
    $writeGroup->post('/customers', [CustomerController::class, 'create']);
    $writeGroup->put('/customers/{id}', [CustomerController::class, 'update']);
    $writeGroup->delete('/customers/{id}', [CustomerController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
