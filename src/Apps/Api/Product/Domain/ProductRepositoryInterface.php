<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain;

interface ProductRepositoryInterface
{
    public function findById(string $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    /**
     * Filtros soportados: activeOnly, featured, categoryId, providerId, q, lowStock.
     *
     * @param array<string, mixed> $filters
     * @return Product[]
     */
    public function findAll(array $filters = []): array;

    /**
     * @return Product[]
     */
    public function findLowStock(): array;

    public function existsBySlug(string $slug, ?string $excludeId = null): bool;

    public function existsBySku(?string $sku, ?string $excludeId = null): bool;

    public function save(Product $product): void;

    public function delete(string $id): void;

    /**
     * Ajusta el stock de forma atomica.
     *
     * @param array<string, int> $changes productId => delta (puede ser negativo)
     */
    public function adjustStock(array $changes): void;
}
