<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api', function (RouteCollectorProxy $group): void {
    $group->get('/ping', function (Request $request, Response $response): Response {
        $payload = [
            'status' => 'ok',
            'time' => date('c'),
        ];

        $response->getBody()->write((string) json_encode($payload));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    });

    require __DIR__ . '/Auth/Infrastructure/routes.php';
    require __DIR__ . '/Page/Infrastructure/routes.php';
    require __DIR__ . '/Inquiry/Infrastructure/routes.php';
    require __DIR__ . '/Category/Infrastructure/routes.php';
    require __DIR__ . '/Provider/Infrastructure/routes.php';
    require __DIR__ . '/Product/Infrastructure/routes.php';
    require __DIR__ . '/Media/Infrastructure/routes.php';
    require __DIR__ . '/Customer/Infrastructure/routes.php';
    require __DIR__ . '/Quote/Infrastructure/routes.php';
    require __DIR__ . '/Invoice/Infrastructure/routes.php';
    require __DIR__ . '/Maintenance/Infrastructure/routes.php';
    require __DIR__ . '/Migration/Infrastructure/routes.php';
});
