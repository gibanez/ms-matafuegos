<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\MarkNotified;

use Gaby\Apps\Api\Maintenance\Domain\Exception\MaintenanceReminderNotFoundException;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;

final class MarkNotifiedHandler
{
    /** @var MaintenanceRepositoryInterface */
    private $reminders;

    public function __construct(MaintenanceRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    public function handle(MarkNotifiedCommand $command): void
    {
        $reminder = $this->reminders->findById($command->id);

        if ($reminder === null) {
            throw MaintenanceReminderNotFoundException::withId($command->id);
        }

        if ($reminder->notifiedAt() !== null) {
            return;
        }

        $this->reminders->markNotified($reminder->id(), date('Y-m-d'), date('c'));
    }
}
