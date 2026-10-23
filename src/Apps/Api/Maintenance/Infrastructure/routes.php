<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Maintenance\Infrastructure\Http\MaintenanceController;
use Slim\Routing\RouteCollectorProxy;

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->get('/maintenance/calendar', [MaintenanceController::class, 'calendar']);
    $writeGroup->post('/maintenance/{id}/notify', [MaintenanceController::class, 'notify']);
    $writeGroup->post('/maintenance/{id}/snooze', [MaintenanceController::class, 'snooze']);
})->add(JwtAuthMiddleware::class);
