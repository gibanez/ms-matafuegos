<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Quote\Infrastructure\Http\QuoteController;
use Slim\Routing\RouteCollectorProxy;

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/quotes', [QuoteController::class, 'list']);
    $writeGroup->get('/quotes/{id}', [QuoteController::class, 'show']);
    $writeGroup->post('/quotes', [QuoteController::class, 'create']);
    $writeGroup->put('/quotes/{id}', [QuoteController::class, 'update']);
    $writeGroup->patch('/quotes/{id}/status', [QuoteController::class, 'changeStatus']);
    $writeGroup->delete('/quotes/{id}', [QuoteController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
