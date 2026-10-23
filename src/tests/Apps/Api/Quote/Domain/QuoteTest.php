<?php

declare(strict_types=1);

use Gaby\Apps\Api\Quote\Domain\Exception\InvalidQuoteException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteAdjustment;
use Gaby\Apps\Api\Quote\Domain\QuoteLine;

test('calcula subtotal, ajustes y total sin composicion', function (): void {
    $lines = [
        QuoteLine::create('product-1', 'Matafuego', 2, 4500000),
        QuoteLine::create(null, 'Recarga', 1, 800000),
    ];
    $adjustments = [
        QuoteAdjustment::create('Envio', QuoteAdjustment::KIND_SURCHARGE, QuoteAdjustment::VALUE_FIXED, 300000),
        QuoteAdjustment::create('Descuento', QuoteAdjustment::KIND_DISCOUNT, QuoteAdjustment::VALUE_PERCENTAGE, 500),
    ];

    $quote = Quote::create(
        'PRE-000001',
        1,
        'customer-1',
        '2026-10-21',
        '2026-11-20',
        'Nota',
        Quote::STATUS_DRAFT,
        $lines,
        $adjustments,
        '2026-10-21T00:00:00-03:00'
    );

    expect($quote->subtotalCents())->toBe(9800000);
    expect($quote->adjustmentsTotalCents())->toBe(-190000);
    expect($quote->totalCents())->toBe(9610000);
});

test('rechaza ajustes que dejan el total negativo', function (): void {
    $lines = [
        QuoteLine::create(null, 'Servicio', 1, 1000),
    ];
    $adjustments = [
        QuoteAdjustment::create('Descuento', QuoteAdjustment::KIND_DISCOUNT, QuoteAdjustment::VALUE_FIXED, 2000),
    ];

    expect(function () use ($lines, $adjustments): void {
        Quote::create(
            'PRE-000002',
            2,
            'customer-1',
            '2026-10-21',
            null,
            null,
            Quote::STATUS_DRAFT,
            $lines,
            $adjustments,
            '2026-10-21T00:00:00-03:00'
        );
    })->toThrow(InvalidQuoteException::class);
});

test('rechaza un rango de vigencia invertido', function (): void {
    expect(function (): void {
        Quote::create(
            'PRE-000003',
            3,
            'customer-1',
            '2026-10-21',
            '2026-10-20',
            null,
            Quote::STATUS_DRAFT,
            [QuoteLine::create(null, 'Servicio', 1, 1000)],
            [],
            '2026-10-21T00:00:00-03:00'
        );
    })->toThrow(InvalidQuoteException::class);
});

test('no permite modificar un presupuesto cancelado', function (): void {
    $quote = Quote::create(
        'PRE-000004',
        4,
        'customer-1',
        '2026-10-21',
        null,
        null,
        Quote::STATUS_DRAFT,
        [QuoteLine::create(null, 'Servicio', 1, 1000)],
        [],
        '2026-10-21T00:00:00-03:00'
    );

    $quote->changeStatus(Quote::STATUS_CANCELLED, '2026-10-21T01:00:00-03:00');

    expect(function () use ($quote): void {
        $quote->changeStatus(Quote::STATUS_ACCEPTED, '2026-10-21T02:00:00-03:00');
    })->toThrow(\Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotEditableException::class);
});
