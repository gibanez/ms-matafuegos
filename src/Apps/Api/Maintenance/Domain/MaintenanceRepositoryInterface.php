<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Domain;

interface MaintenanceRepositoryInterface
{
    /**
     * @return MaintenanceReminder[]
     */
    public function findAll(): array;

    public function findById(string $invoiceLineId): ?MaintenanceReminder;

    public function markNotified(string $invoiceLineId, string $today, string $now): void;

    public function snooze(string $invoiceLineId, string $until, string $now): void;
}
