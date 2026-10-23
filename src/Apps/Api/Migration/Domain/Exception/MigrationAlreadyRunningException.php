<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Domain\Exception;

use RuntimeException;

final class MigrationAlreadyRunningException extends RuntimeException
{
    public static function create(): self
    {
        return new self('A migration is already running. Try again in a moment.');
    }
}
