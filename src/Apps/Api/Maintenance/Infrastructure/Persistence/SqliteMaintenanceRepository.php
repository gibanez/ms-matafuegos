<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Infrastructure\Persistence;

use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;
use PDO;

final class SqliteMaintenanceRepository implements MaintenanceRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Builds one reminder per customer + product, based on the latest paid invoice
     * that contains a product requiring maintenance.
     *
     * @return MaintenanceReminder[]
     */
    public function findAll(): array
    {
        $statement = $this->pdo->query(
            "SELECT il.id AS line_id,
                    i.id AS invoice_id,
                    i.number AS invoice_number,
                    i.paid_at,
                    i.sequence,
                    i.customer_id,
                    c.name AS customer_name,
                    c.phone AS customer_phone,
                    p.id AS product_id,
                    p.name AS product_name,
                    p.maintenance_days,
                    n.notified_at,
                    n.snoozed_until
             FROM invoice_lines il
             INNER JOIN invoices i ON i.id = il.invoice_id
             INNER JOIN products p ON p.id = il.product_id
             INNER JOIN customers c ON c.id = i.customer_id
             LEFT JOIN maintenance_notices n ON n.invoice_line_id = il.id
             WHERE i.status = 'paid'
               AND i.paid_at IS NOT NULL
               AND p.requires_maintenance = 1
             ORDER BY i.paid_at ASC, i.sequence ASC, il.rowid ASC"
        );

        $latest = [];

        foreach ($statement->fetchAll() as $row) {
            $key = (string) $row['customer_id'] . '|' . (string) $row['product_id'];
            $latest[$key] = $row;
        }

        $reminders = [];

        foreach ($latest as $row) {
            $reminders[] = new MaintenanceReminder(
                (string) $row['line_id'],
                (string) $row['invoice_id'],
                (string) $row['invoice_number'],
                (string) $row['customer_id'],
                (string) $row['customer_name'],
                $row['customer_phone'] === null ? null : (string) $row['customer_phone'],
                (string) $row['product_id'],
                (string) $row['product_name'],
                (string) $row['paid_at'],
                (int) $row['maintenance_days'],
                $row['notified_at'] === null ? null : (string) $row['notified_at'],
                $row['snoozed_until'] === null ? null : (string) $row['snoozed_until']
            );
        }

        return $reminders;
    }

    public function findById(string $invoiceLineId): ?MaintenanceReminder
    {
        foreach ($this->findAll() as $reminder) {
            if ($reminder->id() === $invoiceLineId) {
                return $reminder;
            }
        }

        return null;
    }

    public function markNotified(string $invoiceLineId, string $today, string $now): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO maintenance_notices
                (invoice_line_id, notified_at, snoozed_until, notes, created_at, updated_at)
             VALUES
                (:invoice_line_id, :notified_at, NULL, NULL, :created_at, :updated_at)
             ON CONFLICT(invoice_line_id) DO UPDATE SET
                notified_at = excluded.notified_at,
                snoozed_until = NULL,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'invoice_line_id' => $invoiceLineId,
            'notified_at' => $today,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function snooze(string $invoiceLineId, string $until, string $now): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO maintenance_notices
                (invoice_line_id, notified_at, snoozed_until, notes, created_at, updated_at)
             VALUES
                (:invoice_line_id, NULL, :snoozed_until, NULL, :created_at, :updated_at)
             ON CONFLICT(invoice_line_id) DO UPDATE SET
                notified_at = NULL,
                snoozed_until = excluded.snoozed_until,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'invoice_line_id' => $invoiceLineId,
            'snoozed_until' => $until,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
