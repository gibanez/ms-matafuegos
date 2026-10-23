<?php

declare(strict_types=1);

use Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException;
use Gaby\Apps\Api\Product\Domain\Product;

test('oculta el precio en la vista publica cuando showPrice es falso', function (): void {
    $product = Product::create(
        'Matafuego ABC',
        'matafuego-abc',
        null,
        null,
        null,
        null,
        4500000,
        false,
        10,
        3,
        true,
        false,
        365,
        Product::STATUS_ACTIVE,
        '2026-10-21T00:00:00-03:00'
    );

    $public = $product->toArray(true);
    $admin = $product->toArray();

    expect($public['price'])->toBeNull();
    expect($public['showPrice'])->toBeFalse();
    expect($admin['price'])->toBe(45000.0);
});

test('expone los datos de mantenimiento en la vista de gestion', function (): void {
    $product = Product::create(
        'Recarga de matafuego',
        'recarga-de-matafuego',
        null,
        null,
        null,
        null,
        800000,
        true,
        0,
        0,
        false,
        true,
        180,
        Product::STATUS_ACTIVE,
        '2026-10-21T00:00:00-03:00'
    );

    $admin = $product->toArray();

    expect($admin['requiresMaintenance'])->toBeTrue();
    expect($admin['maintenanceDays'])->toBe(180);
    expect($product->requiresMaintenance())->toBeTrue();
    expect($product->maintenanceDays())->toBe(180);
});

test('marca stock bajo cuando el stock no supera el minimo', function (): void {
    $product = Product::create(
        'Matafuego ABC',
        'matafuego-abc',
        null,
        null,
        null,
        null,
        4500000,
        true,
        3,
        3,
        false,
        false,
        365,
        Product::STATUS_ACTIVE,
        '2026-10-21T00:00:00-03:00'
    );

    expect($product->isLowStock())->toBeTrue();
    expect($product->isInStock())->toBeTrue();
});

test('ajusta el stock y rechaza dejar el stock negativo', function (): void {
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

    $product->adjustStock(-4);
    expect($product->stock())->toBe(6);

    expect(function () use ($product): void {
        $product->adjustStock(-7);
    })->toThrow(InsufficientStockException::class);
});
