<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Domain\Exception;

use RuntimeException;

final class InvoiceNotEditableException extends RuntimeException
{
    public static function notDraft(string $id): self
    {
        return new self('Only draft invoices can be edited: ' . $id);
    }

    public static function cancelled(string $id): self
    {
        return new self('Invoice is cancelled and cannot be changed: ' . $id);
    }
}
