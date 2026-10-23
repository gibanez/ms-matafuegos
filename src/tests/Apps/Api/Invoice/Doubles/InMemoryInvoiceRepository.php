<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Invoice\Doubles;

use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    /** @var array<string, Invoice> */
    private $invoices = [];

    /** @var int */
    private $sequence = 1;

    public function findById(string $id): ?Invoice
    {
        return $this->invoices[$id] ?? null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return Invoice[]
     */
    public function findAll(array $filters = []): array
    {
        $invoices = [];

        foreach ($this->invoices as $invoice) {
            if (!empty($filters['status']) && $invoice->status() !== $filters['status']) {
                continue;
            }

            if (!empty($filters['customerId']) && $invoice->customerId() !== $filters['customerId']) {
                continue;
            }

            if (!empty($filters['quoteId']) && $invoice->quoteId() !== $filters['quoteId']) {
                continue;
            }

            if (!empty($filters['from']) && $invoice->issueDate() < $filters['from']) {
                continue;
            }

            if (!empty($filters['to']) && $invoice->issueDate() > $filters['to']) {
                continue;
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    public function nextSequence(): int
    {
        return $this->sequence++;
    }

    public function save(Invoice $invoice): void
    {
        $this->invoices[$invoice->id()] = $invoice;

        if ($invoice->sequence() >= $this->sequence) {
            $this->sequence = $invoice->sequence() + 1;
        }
    }

    public function delete(string $id): void
    {
        unset($this->invoices[$id]);
    }

    public function existsByCustomerId(string $customerId): bool
    {
        foreach ($this->invoices as $invoice) {
            if ($invoice->customerId() === $customerId) {
                return true;
            }
        }

        return false;
    }

    public function existsByQuoteId(string $quoteId): bool
    {
        foreach ($this->invoices as $invoice) {
            if ($invoice->quoteId() === $quoteId) {
                return true;
            }
        }

        return false;
    }
}
