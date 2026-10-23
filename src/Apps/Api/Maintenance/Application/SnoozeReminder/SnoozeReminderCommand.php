<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\SnoozeReminder;

final class SnoozeReminderCommand
{
    /** @var string */
    public $id;

    /** @var int */
    public $days;

    public function __construct(string $id, int $days)
    {
        $this->id = $id;
        $this->days = $days;
    }
}
