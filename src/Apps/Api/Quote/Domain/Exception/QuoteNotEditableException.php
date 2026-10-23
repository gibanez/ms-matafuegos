<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain\Exception;

use RuntimeException;

final class QuoteNotEditableException extends RuntimeException
{
    public static function cancelled(string $id): self
    {
        return new self('Quote is cancelled and cannot be edited: ' . $id);
    }
}
