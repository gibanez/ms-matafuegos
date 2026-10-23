<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Domain\Exception;

use RuntimeException;

final class MaintenanceReminderNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Maintenance reminder "%s" not found.', $id));
    }
}
