<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain\Exception;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public static function forProduct(string $id): self
    {
        return new self('Insufficient stock for product: ' . $id);
    }
}
