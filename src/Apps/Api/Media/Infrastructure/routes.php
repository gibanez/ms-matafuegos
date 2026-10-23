<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Media\Infrastructure\Http\MediaController;
use Slim\Routing\RouteCollectorProxy;

$group->get('/products/{id}/media', [MediaController::class, 'list']);

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->post('/products/{id}/media', [MediaController::class, 'create']);
    $writeGroup->put('/products/{id}/media/{mediaId}', [MediaController::class, 'update']);
    $writeGroup->delete('/products/{id}/media/{mediaId}', [MediaController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
