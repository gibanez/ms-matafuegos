<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain\Exception;

use RuntimeException;

final class SkuAlreadyInUseException extends RuntimeException
{
    public static function withSku(string $sku): self
    {
        return new self('SKU already in use: ' . $sku);
    }
}
