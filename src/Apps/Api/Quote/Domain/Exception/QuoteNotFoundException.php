<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain\Exception;

use RuntimeException;

final class QuoteNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Quote not found: ' . $id);
    }
}
