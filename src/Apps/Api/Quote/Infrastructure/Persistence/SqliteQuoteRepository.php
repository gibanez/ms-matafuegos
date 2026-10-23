<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Infrastructure\Persistence;

use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteAdjustment;
use Gaby\Apps\Api\Quote\Domain\QuoteLine;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;
use PDO;
use Throwable;

final class SqliteQuoteRepository implements QuoteRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Quote
    {
        $statement = $this->pdo->prepare('SELECT * FROM quotes WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row, $this->loadLines($id), $this->loadAdjustments($id));
    }

    /**
     * @param array<string, mixed> $filters
     * @return Quote[]
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

        if (!empty($filters['from'])) {
            $where[] = 'issue_date >= :from';
            $params['from'] = (string) $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where[] = 'issue_date <= :to';
            $params['to'] = (string) $filters['to'];
        }

        $sql = 'SELECT * FROM quotes';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY sequence DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $quotes = [];

        foreach ($statement->fetchAll() as $row) {
            $id = (string) $row['id'];
            $quotes[] = $this->hydrate($row, $this->loadLines($id), $this->loadAdjustments($id));
        }

        return $quotes;
    }

    public function nextSequence(): int
    {
        return (int) $this->pdo->query('SELECT COALESCE(MAX(sequence), 0) + 1 FROM quotes')->fetchColumn();
    }

    public function save(Quote $quote): void
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO quotes
                    (id, sequence, number, customer_id, issue_date, valid_until, status, notes,
                     subtotal_cents, adjustments_total_cents, total_cents, created_at, updated_at)
                 VALUES
                    (:id, :sequence, :number, :customer_id, :issue_date, :valid_until, :status, :notes,
                     :subtotal_cents, :adjustments_total_cents, :total_cents, :created_at, :updated_at)
                 ON CONFLICT(id) DO UPDATE SET
                    customer_id = excluded.customer_id,
                    issue_date = excluded.issue_date,
                    valid_until = excluded.valid_until,
                    status = excluded.status,
                    notes = excluded.notes,
                    subtotal_cents = excluded.subtotal_cents,
                    adjustments_total_cents = excluded.adjustments_total_cents,
                    total_cents = excluded.total_cents,
                    updated_at = excluded.updated_at'
            );

            $statement->execute([
                'id' => $quote->id(),
                'sequence' => $quote->sequence(),
                'number' => $quote->number(),
                'customer_id' => $quote->customerId(),
                'issue_date' => $quote->issueDate(),
                'valid_until' => $quote->validUntil(),
                'status' => $quote->status(),
                'notes' => $quote->notes(),
                'subtotal_cents' => $quote->subtotalCents(),
                'adjustments_total_cents' => $quote->adjustmentsTotalCents(),
                'total_cents' => $quote->totalCents(),
                'created_at' => $quote->createdAt(),
                'updated_at' => $quote->updatedAt(),
            ]);

            $deleteLines = $this->pdo->prepare('DELETE FROM quote_lines WHERE quote_id = :quote_id');
            $deleteLines->execute(['quote_id' => $quote->id()]);

            $deleteAdjustments = $this->pdo->prepare('DELETE FROM quote_adjustments WHERE quote_id = :quote_id');
            $deleteAdjustments->execute(['quote_id' => $quote->id()]);

            $insertLine = $this->pdo->prepare(
                'INSERT INTO quote_lines
                    (id, quote_id, product_id, description, quantity, unit_price_cents, line_total_cents, position)
                 VALUES
                    (:id, :quote_id, :product_id, :description, :quantity, :unit_price_cents, :line_total_cents, :position)'
            );

            foreach ($quote->lines() as $position => $line) {
                $insertLine->execute([
                    'id' => $line->id(),
                    'quote_id' => $quote->id(),
                    'product_id' => $line->productId(),
                    'description' => $line->description(),
                    'quantity' => $line->quantity(),
                    'unit_price_cents' => $line->unitPriceCents(),
                    'line_total_cents' => $line->lineTotalCents(),
                    'position' => $position,
                ]);
            }

            $insertAdjustment = $this->pdo->prepare(
                'INSERT INTO quote_adjustments
                    (id, quote_id, description, kind, value_type, value, amount_cents, position)
                 VALUES
                    (:id, :quote_id, :description, :kind, :value_type, :value, :amount_cents, :position)'
            );

            foreach ($quote->adjustments() as $position => $adjustment) {
                $insertAdjustment->execute([
                    'id' => $adjustment->id(),
                    'quote_id' => $quote->id(),
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
        $statement = $this->pdo->prepare('DELETE FROM quotes WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function existsByCustomerId(string $customerId): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM quotes WHERE customer_id = :customer_id');
        $statement->execute(['customer_id' => $customerId]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @return QuoteLine[]
     */
    private function loadLines(string $quoteId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM quote_lines WHERE quote_id = :quote_id ORDER BY position ASC, rowid ASC'
        );
        $statement->execute(['quote_id' => $quoteId]);
        $lines = [];

        foreach ($statement->fetchAll() as $row) {
            $lines[] = QuoteLine::fromPersistence(
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
     * @return QuoteAdjustment[]
     */
    private function loadAdjustments(string $quoteId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM quote_adjustments WHERE quote_id = :quote_id ORDER BY position ASC, rowid ASC'
        );
        $statement->execute(['quote_id' => $quoteId]);
        $adjustments = [];

        foreach ($statement->fetchAll() as $row) {
            $adjustments[] = QuoteAdjustment::fromPersistence(
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
     * @param QuoteLine[] $lines
     * @param QuoteAdjustment[] $adjustments
     */
    private function hydrate(array $row, array $lines, array $adjustments): Quote
    {
        return Quote::fromPersistence(
            (string) $row['id'],
            (string) $row['number'],
            (int) $row['sequence'],
            (string) $row['customer_id'],
            (string) $row['issue_date'],
            $row['valid_until'] === null ? null : (string) $row['valid_until'],
            (string) $row['status'],
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
