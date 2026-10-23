<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Infrastructure\Persistence;

use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceAdjustment;
use Gaby\Apps\Api\Invoice\Domain\InvoiceLine;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;
use PDO;
use Throwable;

final class SqliteInvoiceRepository implements InvoiceRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Invoice
    {
        $statement = $this->pdo->prepare('SELECT * FROM invoices WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row, $this->loadLines($id), $this->loadAdjustments($id));
    }

    /**
     * @param array<string, mixed> $filters
     * @return Invoice[]
     */
    public function findAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = (string) $filters['status'];
        }

        if (!empty($filters['customerId'])) {
            $where[] = 'customer_id = :customer_id';
            $params['customer_id'] = (string) $filters['customerId'];
        }

        if (!empty($filters['quoteId'])) {
            $where[] = 'quote_id = :quote_id';
            $params['quote_id'] = (string) $filters['quoteId'];
        }

        if (!empty($filters['from'])) {
            $where[] = 'issue_date >= :from';
            $params['from'] = (string) $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where[] = 'issue_date <= :to';
            $params['to'] = (string) $filters['to'];
        }

        $sql = 'SELECT * FROM invoices';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY sequence DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $invoices = [];

        foreach ($statement->fetchAll() as $row) {
            $id = (string) $row['id'];
            $invoices[] = $this->hydrate($row, $this->loadLines($id), $this->loadAdjustments($id));
        }

        return $invoices;
    }

    public function nextSequence(): int
    {
        return (int) $this->pdo->query('SELECT COALESCE(MAX(sequence), 0) + 1 FROM invoices')->fetchColumn();
    }

    public function save(Invoice $invoice): void
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO invoices
                    (id, sequence, number, customer_id, quote_id, issue_date, due_date, paid_at, status, payment_method,
                     notes, subtotal_cents, adjustments_total_cents, total_cents, created_at, updated_at)
                 VALUES
                    (:id, :sequence, :number, :customer_id, :quote_id, :issue_date, :due_date, :paid_at, :status, :payment_method,
                     :notes, :subtotal_cents, :adjustments_total_cents, :total_cents, :created_at, :updated_at)
                 ON CONFLICT(id) DO UPDATE SET
                    customer_id = excluded.customer_id,
                    quote_id = excluded.quote_id,
                    issue_date = excluded.issue_date,
                    due_date = excluded.due_date,
                    paid_at = excluded.paid_at,
                    status = excluded.status,
                    payment_method = excluded.payment_method,
                    notes = excluded.notes,
                    subtotal_cents = excluded.subtotal_cents,
                    adjustments_total_cents = excluded.adjustments_total_cents,
                    total_cents = excluded.total_cents,
                    updated_at = excluded.updated_at'
            );

            $statement->execute([
                'id' => $invoice->id(),
                'sequence' => $invoice->sequence(),
                'number' => $invoice->number(),
                'customer_id' => $invoice->customerId(),
                'quote_id' => $invoice->quoteId(),
                'issue_date' => $invoice->issueDate(),
                'due_date' => $invoice->dueDate(),
                'paid_at' => $invoice->paidAt(),
                'status' => $invoice->status(),
                'payment_method' => $invoice->paymentMethod(),
                'notes' => $invoice->notes(),
                'subtotal_cents' => $invoice->subtotalCents(),
                'adjustments_total_cents' => $invoice->adjustmentsTotalCents(),
                'total_cents' => $invoice->totalCents(),
                'created_at' => $invoice->createdAt(),
                'updated_at' => $invoice->updatedAt(),
            ]);

            $deleteLines = $this->pdo->prepare('DELETE FROM invoice_lines WHERE invoice_id = :invoice_id');
            $deleteLines->execute(['invoice_id' => $invoice->id()]);

            $deleteAdjustments = $this->pdo->prepare('DELETE FROM invoice_adjustments WHERE invoice_id = :invoice_id');
            $deleteAdjustments->execute(['invoice_id' => $invoice->id()]);

            $insertLine = $this->pdo->prepare(
                'INSERT INTO invoice_lines
                    (id, invoice_id, product_id, description, quantity, unit_price_cents, line_total_cents, position)
                 VALUES
                    (:id, :invoice_id, :product_id, :description, :quantity, :unit_price_cents, :line_total_cents, :position)'
            );

            foreach ($invoice->lines() as $position => $line) {
                $insertLine->execute([
                    'id' => $line->id(),
                    'invoice_id' => $invoice->id(),
                    'product_id' => $line->productId(),
                    'description' => $line->description(),
                    'quantity' => $line->quantity(),
                    'unit_price_cents' => $line->unitPriceCents(),
                    'line_total_cents' => $line->lineTotalCents(),
                    'position' => $position,
                ]);
            }

            $insertAdjustment = $this->pdo->prepare(
                'INSERT INTO invoice_adjustments
                    (id, invoice_id, description, kind, value_type, value, amount_cents, position)
                 VALUES
                    (:id, :invoice_id, :description, :kind, :value_type, :value, :amount_cents, :position)'
            );

            foreach ($invoice->adjustments() as $position => $adjustment) {
                $insertAdjustment->execute([
                    'id' => $adjustment->id(),
                    'invoice_id' => $invoice->id(),
                    'description' => $adjustment->description(),
                    'kind' => $adjustment->kind(),
                    'value_type' => $adjustment->valueType(),
                    'value' => $adjustment->value(),
                    'amount_cents' => $adjustment->amountCents(),
                    'position' => $position,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $error;
        }
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM invoices WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function existsByCustomerId(string $customerId): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM invoices WHERE customer_id = :customer_id');
        $statement->execute(['customer_id' => $customerId]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function existsByQuoteId(string $quoteId): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM invoices WHERE quote_id = :quote_id');
        $statement->execute(['quote_id' => $quoteId]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @return InvoiceLine[]
     */
    private function loadLines(string $invoiceId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM invoice_lines WHERE invoice_id = :invoice_id ORDER BY position ASC, rowid ASC'
        );
        $statement->execute(['invoice_id' => $invoiceId]);
        $lines = [];

        foreach ($statement->fetchAll() as $row) {
            $lines[] = InvoiceLine::fromPersistence(
                (string) $row['id'],
                $row['product_id'] === null ? null : (string) $row['product_id'],
                (string) $row['description'],
                (int) $row['quantity'],
                (int) $row['unit_price_cents'],
                (int) $row['line_total_cents']
            );
        }

        return $lines;
    }

    /**
     * @return InvoiceAdjustment[]
     */
    private function loadAdjustments(string $invoiceId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM invoice_adjustments WHERE invoice_id = :invoice_id ORDER BY position ASC, rowid ASC'
        );
        $statement->execute(['invoice_id' => $invoiceId]);
        $adjustments = [];

        foreach ($statement->fetchAll() as $row) {
            $adjustments[] = InvoiceAdjustment::fromPersistence(
                (string) $row['id'],
                (string) $row['description'],
                (string) $row['kind'],
                (string) $row['value_type'],
                (int) $row['value'],
                (int) $row['amount_cents']
            );
        }

        return $adjustments;
    }

    /**
     * @param array<string, mixed> $row
     * @param InvoiceLine[] $lines
     * @param InvoiceAdjustment[] $adjustments
     */
    private function hydrate(array $row, array $lines, array $adjustments): Invoice
    {
        return Invoice::fromPersistence(
            (string) $row['id'],
            (string) $row['number'],
            (int) $row['sequence'],
            (string) $row['customer_id'],
            $row['quote_id'] === null ? null : (string) $row['quote_id'],
            (string) $row['issue_date'],
            $row['due_date'] === null ? null : (string) $row['due_date'],
            $row['paid_at'] === null ? null : (string) $row['paid_at'],
            (string) $row['status'],
            $row['payment_method'] === null ? null : (string) $row['payment_method'],
            $row['notes'] === null ? null : (string) $row['notes'],
            $lines,
            $adjustments,
            (int) $row['subtotal_cents'],
            (int) $row['adjustments_total_cents'],
            (int) $row['total_cents'],
            (string) $row['created_at'],
            (string) $row['updated_at']
        );
    }
}
