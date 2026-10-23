<?php

declare(strict_types=1);

use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotEditableException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceAdjustment;
use Gaby\Apps\Api\Invoice\Domain\InvoiceLine;

test('crea una factura en borrador y calcula sus totales', function (): void {
    $lines = [
        InvoiceLine::create('product-1', 'Matafuego', 2, 4500000),
        InvoiceLine::create(null, 'Recarga', 1, 800000),
    ];
    $adjustments = [
        InvoiceAdjustment::create('Envio', InvoiceAdjustment::KIND_SURCHARGE, InvoiceAdjustment::VALUE_FIXED, 300000),
        InvoiceAdjustment::create('Descuento', InvoiceAdjustment::KIND_DISCOUNT, InvoiceAdjustment::VALUE_PERCENTAGE, 500),
    ];

    $invoice = Invoice::create(
        'FAC-000001',
        1,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        $lines,
        $adjustments,
        '2026-10-21T00:00:00-03:00'
    );

    expect($invoice->status())->toBe(Invoice::STATUS_DRAFT);
    expect($invoice->hasStockApplied())->toBeFalse();
    expect($invoice->subtotalCents())->toBe(9800000);
    expect($invoice->adjustmentsTotalCents())->toBe(-190000);
    expect($invoice->totalCents())->toBe(9610000);
    expect($invoice->productQuantities())->toBe(['product-1' => 2]);
});

test('no permite saltar de borrador a pagada', function (): void {
    $invoice = Invoice::create(
        'FAC-000002',
        2,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        [InvoiceLine::create(null, 'Servicio', 1, 1000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );

    expect(function () use ($invoice): void {
        $invoice->changeStatus(Invoice::STATUS_PAID, '2026-10-21T01:00:00-03:00');
    })->toThrow(InvalidInvoiceException::class);
});

test('registra la fecha de pago al pasar a pagada', function (): void {
    $invoice = Invoice::create(
        'FAC-000004',
        4,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        [InvoiceLine::create(null, 'Servicio', 1, 1000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );

    expect($invoice->paidAt())->toBeNull();

    $invoice->changeStatus(Invoice::STATUS_ISSUED, '2026-10-22T10:00:00-03:00');
    $invoice->changeStatus(Invoice::STATUS_PAID, '2026-10-25T10:00:00-03:00');

    expect($invoice->paidAt())->toBe('2026-10-25');
    expect($invoice->toArray()['paidAt'])->toBe('2026-10-25');
});

test('solo permite editar una factura en borrador', function (): void {
    $invoice = Invoice::create(
        'FAC-000003',
        3,
        'customer-1',
        null,
        '2026-10-21',
        null,
        null,
        null,
        [InvoiceLine::create(null, 'Servicio', 1, 1000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );

    $invoice->changeStatus(Invoice::STATUS_ISSUED, '2026-10-21T01:00:00-03:00');

    expect(function () use ($invoice): void {
        $invoice->updateDetails(
            'customer-1',
            '2026-10-21',
            null,
            null,
            null,
            [InvoiceLine::create(null, 'Otro', 1, 2000)],
            [],
            '2026-10-21T02:00:00-03:00'
        );
    })->toThrow(InvoiceNotEditableException::class);
});
