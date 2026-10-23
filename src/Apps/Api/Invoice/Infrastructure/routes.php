<?php

declare(strict_types=1);

use Gaby\Apps\Api\Auth\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use Gaby\Apps\Api\Invoice\Infrastructure\Http\InvoiceController;
use Slim\Routing\RouteCollectorProxy;

$group->group('', function (RouteCollectorProxy $writeGroup): void {
    $writeGroup->post('/invoices/from-quote/{quoteId}', [InvoiceController::class, 'fromQuote']);
    $writeGroup->get('/invoices', [InvoiceController::class, 'list']);
    $writeGroup->get('/invoices/{id}', [InvoiceController::class, 'show']);
    $writeGroup->post('/invoices', [InvoiceController::class, 'create']);
    $writeGroup->put('/invoices/{id}', [InvoiceController::class, 'update']);
    $writeGroup->patch('/invoices/{id}/status', [InvoiceController::class, 'changeStatus']);
    $writeGroup->delete('/invoices/{id}', [InvoiceController::class, 'delete']);
})->add(JwtAuthMiddleware::class);
