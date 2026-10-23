<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Domain\Exception;

use RuntimeException;

final class CustomerTaxIdAlreadyInUseException extends RuntimeException
{
    public static function withTaxId(string $taxId): self
    {
        return new self('Customer tax id already in use: ' . $taxId);
    }
}
