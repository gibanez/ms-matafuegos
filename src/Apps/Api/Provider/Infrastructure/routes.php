<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Provider\Infrastructure\Http\ProviderController;
use Slim\Routing\RouteCollectorProxy;

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/providers', [ProviderController::class, 'list']);
    $writeGroup->get('/providers/{id}', [ProviderController::class, 'show']);
    $writeGroup->post('/providers', [ProviderController::class, 'create']);
    $writeGroup->put('/providers/{id}', [ProviderController::class, 'update']);
    $writeGroup->delete('/providers/{id}', [ProviderController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
