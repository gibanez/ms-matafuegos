<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Domain\Exception;

use RuntimeException;

final class ProviderNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Provider not found: ' . $id);
    }
}
