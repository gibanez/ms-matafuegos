<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Domain\Exception;

use RuntimeException;

final class CategorySlugAlreadyInUseException extends RuntimeException
{
    public static function withSlug(string $slug): self
    {
        return new self('Category slug already in use: ' . $slug);
    }
}
