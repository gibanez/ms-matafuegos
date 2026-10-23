<?php

declare(strict_types=1);

namespace Gaby\Shared\Domain;

use InvalidArgumentException;

/**
 * Utilidades para trabajar con importes en centavos.
 *
 * Toda la persistencia y los calculos internos usan enteros (centavos).
 * El JSON de la API expone decimales con dos posiciones.
 */
final class Money
{
    /**
     * Convierte un valor decimal (int, float o string) a centavos.
     *
     * Acepta formatos como 1234.56, "1234,56" y "$ 1.234,56".
     *
     * @param mixed $value
     */
    public static function toCents($value): int
    {
        return (int) round(self::parseDecimal($value) * 100);
    }

    /**
     * Convierte un porcentaje (5, "5" o "5%") a puntos basicos (500).
     *
     * @param mixed $value
     */
    public static function percentageToBasisPoints($value): int
    {
        $normalized = trim((string) $value);
        $normalized = str_replace('%', '', $normalized);

        if ($normalized === '') {
            throw new InvalidArgumentException('Invalid percentage.');
        }

        return (int) round(self::parseDecimal($normalized) * 100);
    }

    public static function toDecimal(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * @param mixed $value
     */
    private static function parseDecimal($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace(['$', ' ', "\xc2\xa0"], '', $normalized);

        if ($normalized === '') {
            throw new InvalidArgumentException('Invalid amount.');
        }

        $hasComma = strpos($normalized, ',') !== false;
        $hasDot = strpos($normalized, '.') !== false;

        if ($hasComma && $hasDot) {
            // Formato 1.234,56: el punto es separador de miles.
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (!is_numeric($normalized)) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        return (float) $normalized;
    }
}
