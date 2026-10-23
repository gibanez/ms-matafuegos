<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Domain;

interface InvoiceRepositoryInterface
{
    public function findById(string $id): ?Invoice;

    /**
     * Filtros soportados: status, customerId, from, to, quoteId.
     *
     * @param array<string, mixed> $filters
     * @return Invoice[]
     */
    public function findAll(array $filters = []): array;

    public function nextSequence(): int;

    public function save(Invoice $invoice): void;

    public function delete(string $id): void;

    public function existsByCustomerId(string $customerId): bool;

    public function existsByQuoteId(string $quoteId): bool;
}
