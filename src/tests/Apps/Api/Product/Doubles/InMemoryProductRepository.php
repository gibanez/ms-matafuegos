<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Product\Doubles;

use Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /** @var array<string, Product> */
    private $products = [];

    public function findById(string $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->slug() === $slug) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return Product[]
     */
    public function findAll(array $filters = []): array
    {
        $products = [];

        foreach ($this->products as $product) {
            if (!empty($filters['activeOnly']) && !$product->isActive()) {
                continue;
            }

            if (!empty($filters['featured']) && !$product->featured()) {
                continue;
            }

            if (!empty($filters['categoryId']) && $product->categoryId() !== $filters['categoryId']) {
                continue;
            }

            if (!empty($filters['providerId']) && $product->providerId() !== $filters['providerId']) {
                continue;
            }

            if (!empty($filters['lowStock']) && !$product->isLowStock()) {
                continue;
            }

            if (!empty($filters['q']) && stripos($product->name(), (string) $filters['q']) === false) {
                continue;
            }

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return Product[]
     */
    public function findLowStock(): array
    {
        return array_values(array_filter($this->products, function (Product $product): bool {
            return $product->isLowStock();
        }));
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        foreach ($this->products as $product) {
            if ($product->slug() === $slug && ($excludeId === null || $product->id() !== $excludeId)) {
                return true;
            }
        }

        return false;
    }

    public function existsBySku(?string $sku, ?string $excludeId = null): bool
    {
        if ($sku === null) {
            return false;
        }

        foreach ($this->products as $product) {
            if ($product->sku() === $sku && ($excludeId === null || $product->id() !== $excludeId)) {
                return true;
            }
        }

        return false;
    }

    public function save(Product $product): void
    {
        $this->products[$product->id()] = $product;
    }

    public function delete(string $id): void
    {
        unset($this->products[$id]);
    }

    /**
     * @param array<string, int> $changes
     */
    public function adjustStock(array $changes): void
    {
        foreach ($changes as $productId => $delta) {
            $product = $this->products[$productId] ?? null;

            if ($product === null) {
                throw ProductNotFoundException::withId($productId);
            }

            if ($product->stock() + $delta < 0) {
                throw InsufficientStockException::forProduct($productId);
            }
        }

        foreach ($changes as $productId => $delta) {
            $this->products[$productId]->adjustStock($delta);
        }
    }
}
