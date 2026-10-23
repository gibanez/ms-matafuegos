<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Gaby\Shared\Config\Env;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

// Desarrollo con `php -S ... -t public` (sin router): el servidor embebido manda
// las rutas del sitio a este archivo. Servimos la SPA y el admin desde acá para
// que no responda el 404 JSON de Slim.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    if (is_string($requestPath) && preg_match('#^/api(?:/|$)#', $requestPath) !== 1) {
        if (preg_match('#^/admin(?:/|$)#', $requestPath) === 1) {
            $adminIndex = __DIR__ . '/admin/index.html';

            if (is_file($adminIndex)) {
                header('Content-Type: text/html; charset=utf-8');
                readfile($adminIndex);

                return;
            }
        }

        $siteIndex = __DIR__ . '/index.html';

        if (is_file($siteIndex)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($siteIndex);

            return;
        }
    }
}

require __DIR__ . '/../vendor/autoload.php';

// Algunos hostings compartidos entregan el header Authorization como
// REDIRECT_HTTP_AUTHORIZATION después del rewrite. Normalizarlo para que
// Slim lo vea como header de la request.
if (!isset($_SERVER['HTTP_AUTHORIZATION']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

$timezone = Env::get('APP_TIMEZONE', 'America/Argentina/Buenos_Aires');
date_default_timezone_set($timezone === null || $timezone === '' ? 'UTC' : $timezone);

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../src/Apps/definitions.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$debug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

$errorMiddleware = $app->addErrorMiddleware($debug, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($app, $debug): ResponseInterface {
        $response = $app->getResponseFactory()->createResponse();
        $status = $exception instanceof HttpNotFoundException ? 404 : 500;
        $payload = ['error' => $status === 404 ? 'Not found.' : 'Internal server error.'];

        if ($debug) {
            $payload['detail'] = $exception->getMessage();
        }

        $response->getBody()->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withStatus($status);
    }
);

(require __DIR__ . '/../src/Apps/routes.php')($app);

$app->run();
