<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain\Exception;

use RuntimeException;

final class InvalidProductReferenceException extends RuntimeException
{
    public static function categoryNotFound(string $id): self
    {
        return new self('Category not found: ' . $id);
    }

    public static function providerNotFound(string $id): self
    {
        return new self('Provider not found: ' . $id);
    }
}
