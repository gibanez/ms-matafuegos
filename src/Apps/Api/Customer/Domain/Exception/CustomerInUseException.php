<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Domain\Exception;

use RuntimeException;

final class CustomerInUseException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Customer has quotes or invoices and cannot be deleted: ' . $id);
    }
}
