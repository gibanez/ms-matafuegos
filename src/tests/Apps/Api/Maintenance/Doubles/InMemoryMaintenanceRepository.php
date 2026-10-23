<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Maintenance\Doubles;

use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;

final class InMemoryMaintenanceRepository implements MaintenanceRepositoryInterface
{
    /** @var array<string, MaintenanceReminder> */
    private $reminders = [];

    /**
     * @param MaintenanceReminder[] $reminders
     */
    public function __construct(array $reminders = [])
    {
        foreach ($reminders as $reminder) {
            $this->add($reminder);
        }
    }

    public function add(MaintenanceReminder $reminder): void
    {
        $this->reminders[$reminder->id()] = $reminder;
    }

    /**
     * @return MaintenanceReminder[]
     */
    public function findAll(): array
    {
        return array_values($this->reminders);
    }

    public function findById(string $invoiceLineId): ?MaintenanceReminder
    {
        return $this->reminders[$invoiceLineId] ?? null;
    }

    public function markNotified(string $invoiceLineId, string $today, string $now): void
    {
        $this->reminders[$invoiceLineId]->markNotified($today);
    }

    public function snooze(string $invoiceLineId, string $until, string $now): void
    {
        $this->reminders[$invoiceLineId]->snooze($until);
    }
}
