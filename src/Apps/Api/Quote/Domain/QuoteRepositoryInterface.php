<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain;

interface QuoteRepositoryInterface
{
    public function findById(string $id): ?Quote;

    /**
     * Filtros soportados: status, customerId, from, to.
     *
     * @param array<string, mixed> $filters
     * @return Quote[]
     */
    public function findAll(array $filters = []): array;

    public function nextSequence(): int;

    public function save(Quote $quote): void;

    public function delete(string $id): void;

    public function existsByCustomerId(string $customerId): bool;
}
