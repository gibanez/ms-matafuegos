<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Domain\Exception;

use RuntimeException;

final class CustomerNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Customer not found: ' . $id);
    }
}
