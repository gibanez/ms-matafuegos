<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\GetCalendar;

use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;

final class GetCalendarHandler
{
    /** @var MaintenanceRepositoryInterface */
    private $reminders;

    public function __construct(MaintenanceRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(GetCalendarQuery $query): array
    {
        $today = date('Y-m-d');
        $items = [];
        $overdue = [];

        foreach ($this->reminders->findAll() as $reminder) {
            if ($reminder->notifyDate() >= $query->from && $reminder->notifyDate() <= $query->to) {
                $items[] = $reminder;
            }

            if ($reminder->status($today) === MaintenanceReminder::STATUS_OVERDUE) {
                $overdue[] = $reminder;
            }
        }

        usort($items, [$this, 'compare']);
        usort($overdue, [$this, 'compare']);

        return [
            'today' => $today,
            'items' => $items,
            'overdue' => $overdue,
        ];
    }

    private function compare(MaintenanceReminder $left, MaintenanceReminder $right): int
    {
        $dateComparison = strcmp($left->notifyDate(), $right->notifyDate());

        if ($dateComparison !== 0) {
            return $dateComparison;
        }

        return strcmp($left->customerName(), $right->customerName());
    }
}
