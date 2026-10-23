<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain\Exception;

use RuntimeException;

final class ProductSlugAlreadyInUseException extends RuntimeException
{
    public static function withSlug(string $slug): self
    {
        return new self('Product slug already in use: ' . $slug);
    }
}
