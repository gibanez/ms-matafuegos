<?php

declare(strict_types=1);

use Gaby\Shared\Domain\Money;

test('convierte importes decimales a centavos', function (): void {
    expect(Money::toCents(1234.56))->toBe(123456);
    expect(Money::toCents('1234,56'))->toBe(123456);
    expect(Money::toCents('$ 1.234,56'))->toBe(123456);
});

test('convierte porcentajes a puntos basicos', function (): void {
    expect(Money::percentageToBasisPoints(5))->toBe(500);
    expect(Money::percentageToBasisPoints('5%'))->toBe(500);
    expect(Money::percentageToBasisPoints('12,5'))->toBe(1250);
});

test('expone los centavos como decimales', function (): void {
    expect(Money::toDecimal(96100))->toBe(961.0);
    expect(Money::toDecimal(0))->toBe(0.0);
});

test('rechaza importes invalidos', function (): void {
    expect(function (): void {
        Money::toCents('no-es-un-importe');
    })->toThrow(InvalidArgumentException::class);
});
