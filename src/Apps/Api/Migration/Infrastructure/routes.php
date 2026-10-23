<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Migration\Infrastructure\Http\MigrationController;
use Slim\Routing\RouteCollectorProxy;

$group->group('/migrations', function (RouteCollectorProxy $migrationGroup): void {
    $migrationGroup->get('/status', [MigrationController::class, 'status']);
    $migrationGroup->post('/run', [MigrationController::class, 'run']);
})->add(JwtAuthMiddleware::class);
