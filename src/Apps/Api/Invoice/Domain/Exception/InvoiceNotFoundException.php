<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Domain\Exception;

use RuntimeException;

final class InvoiceNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Invoice not found: ' . $id);
    }
}
