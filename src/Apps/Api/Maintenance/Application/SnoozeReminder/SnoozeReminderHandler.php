<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\SnoozeReminder;

use DateTimeImmutable;
use Gaby\Apps\Api\Maintenance\Domain\Exception\MaintenanceReminderNotFoundException;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;

final class SnoozeReminderHandler
{
    /** @var MaintenanceRepositoryInterface */
    private $reminders;

    public function __construct(MaintenanceRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    public function handle(SnoozeReminderCommand $command): void
    {
        $reminder = $this->reminders->findById($command->id);

        if ($reminder === null) {
            throw MaintenanceReminderNotFoundException::withId($command->id);
        }

        $today = date('Y-m-d');
        $base = $reminder->notifyDate() > $today ? $reminder->notifyDate() : $today;
        $until = (new DateTimeImmutable($base))
            ->modify('+' . max(1, $command->days) . ' days')
            ->format('Y-m-d');

        $this->reminders->snooze($reminder->id(), $until, date('c'));
    }
}
