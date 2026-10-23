<?php

declare(strict_types=1);

use Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus\ChangeInvoiceStatusCommand;
use Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus\ChangeInvoiceStatusHandler;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceLine;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Tests\Apps\Api\Invoice\Doubles\InMemoryInvoiceRepository;
use Gaby\Tests\Apps\Api\Product\Doubles\InMemoryProductRepository;

test('descuenta y repone stock al emitir y cancelar una factura', function (): void {
    $products = new InMemoryProductRepository();
    $product = Product::create(
        'Matafuego ABC',
        'matafuego-abc',
        null,
        null,
        null,
        null,
        4500000,
        true,
        10,
        3,
        false,
        false,
        365,
        Product::STATUS_ACTIVE,
        '2026-10-21T00:00:00-03:00'
    );
    $products->save($product);

    $invoices = new InMemoryInvoiceRepository();
    $invoice = Invoice::create(
        'FAC-000001',
        1,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        [InvoiceLine::create($product->id(), 'Matafuego', 2, 4500000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );
    $invoices->save($invoice);

    $handler = new ChangeInvoiceStatusHandler($invoices, $products);

    $issued = $handler->handle(new ChangeInvoiceStatusCommand($invoice->id(), Invoice::STATUS_ISSUED));
    expect($issued->status())->toBe(Invoice::STATUS_ISSUED);
    expect($products->findById($product->id())->stock())->toBe(8);

    $cancelled = $handler->handle(new ChangeInvoiceStatusCommand($invoice->id(), Invoice::STATUS_CANCELLED));
    expect($cancelled->status())->toBe(Invoice::STATUS_CANCELLED);
    expect($products->findById($product->id())->stock())->toBe(10);
});

test('no emite una factura si no alcanza el stock', function (): void {
    $products = new InMemoryProductRepository();
    $product = Product::create(
        'Matafuego ABC',
        'matafuego-abc',
        null,
        null,
        null,
        null,
        4500000,
        true,
        1,
        0,
        false,
        false,
        365,
        Product::STATUS_ACTIVE,
        '2026-10-21T00:00:00-03:00'
    );
    $products->save($product);

    $invoices = new InMemoryInvoiceRepository();
    $invoice = Invoice::create(
        'FAC-000002',
        2,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        [InvoiceLine::create($product->id(), 'Matafuego', 2, 4500000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );
    $invoices->save($invoice);

    $handler = new ChangeInvoiceStatusHandler($invoices, $products);

    expect(function () use ($handler, $invoice): void {
        $handler->handle(new ChangeInvoiceStatusCommand($invoice->id(), Invoice::STATUS_ISSUED));
    })->toThrow(\Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException::class);

    expect($products->findById($product->id())->stock())->toBe(1);
});
