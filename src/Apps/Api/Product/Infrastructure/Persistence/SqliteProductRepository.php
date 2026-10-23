<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Infrastructure\Persistence;

use Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;
use PDO;
use Throwable;

final class SqliteProductRepository implements ProductRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Product
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?Product
    {
        $statement = $this->pdo->prepare('SELECT * FROM products WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @param array<string, mixed> $filters
     * @return Product[]
     */
    public function findAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['activeOnly'])) {
            $where[] = "status = 'active'";
        }

        if (array_key_exists('featured', $filters) && $filters['featured'] !== null) {
            $where[] = 'featured = :featured';
            $params['featured'] = $filters['featured'] ? 1 : 0;
        }

        if (!empty($filters['categoryId'])) {
            $where[] = 'category_id = :category_id';
            $params['category_id'] = (string) $filters['categoryId'];
        }

        if (!empty($filters['providerId'])) {
            $where[] = 'provider_id = :provider_id';
            $params['provider_id'] = (string) $filters['providerId'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(name LIKE :q OR sku LIKE :q OR description LIKE :q)';
            $params['q'] = '%' . (string) $filters['q'] . '%';
        }

        if (!empty($filters['lowStock'])) {
            $where[] = 'stock <= min_stock';
        }

        $sql = 'SELECT * FROM products';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY name ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    /**
     * @return Product[]
     */
    public function findLowStock(): array
    {
        return $this->findAll(['lowStock' => true]);
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function existsBySku(?string $sku, ?string $excludeId = null): bool
    {
        if ($sku === null || $sku === '') {
            return false;
        }

        $sql = 'SELECT COUNT(*) FROM products WHERE sku = :sku';
        $params = ['sku' => $sku];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function save(Product $product): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products
                (id, name, slug, description, sku, category_id, provider_id, price_cents, show_price,
                 stock, min_stock, featured, requires_maintenance, maintenance_days, status, created_at, updated_at)
             VALUES
                (:id, :name, :slug, :description, :sku, :category_id, :provider_id, :price_cents, :show_price,
                 :stock, :min_stock, :featured, :requires_maintenance, :maintenance_days, :status, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                slug = excluded.slug,
                description = excluded.description,
                sku = excluded.sku,
                category_id = excluded.category_id,
                provider_id = excluded.provider_id,
                price_cents = excluded.price_cents,
                show_price = excluded.show_price,
                stock = excluded.stock,
                min_stock = excluded.min_stock,
                featured = excluded.featured,
                requires_maintenance = excluded.requires_maintenance,
                maintenance_days = excluded.maintenance_days,
                status = excluded.status,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'description' => $product->description(),
            'sku' => $product->sku(),
            'category_id' => $product->categoryId(),
            'provider_id' => $product->providerId(),
            'price_cents' => $product->priceCents(),
            'show_price' => $product->showPrice() ? 1 : 0,
            'stock' => $product->stock(),
            'min_stock' => $product->minStock(),
            'featured' => $product->featured() ? 1 : 0,
            'requires_maintenance' => $product->requiresMaintenance() ? 1 : 0,
            'maintenance_days' => $product->maintenanceDays(),
            'status' => $product->status(),
            'created_at' => $product->createdAt(),
            'updated_at' => $product->updatedAt(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, int> $changes
     */
    public function adjustStock(array $changes): void
    {
        if ($changes === []) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            foreach ($changes as $productId => $delta) {
                $statement = $this->pdo->prepare('SELECT stock FROM products WHERE id = :id');
                $statement->execute(['id' => $productId]);
                $current = $statement->fetchColumn();

                if ($current === false) {
                    throw ProductNotFoundException::withId($productId);
                }

                $newStock = (int) $current + (int) $delta;

                if ($newStock < 0) {
                    throw InsufficientStockException::forProduct($productId);
                }

                $update = $this->pdo->prepare(
                    'UPDATE products SET stock = :stock, updated_at = :updated_at WHERE id = :id'
                );
                $update->execute([
                    'stock' => $newStock,
                    'updated_at' => date('c'),
                    'id' => $productId,
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

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Product
    {
        return Product::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            (string) $row['slug'],
            $this->nullableString($row['description']),
            $this->nullableString($row['sku']),
            $this->nullableString($row['category_id']),
            $this->nullableString($row['provider_id']),
            (int) $row['price_cents'],
            (bool) $row['show_price'],
            (int) $row['stock'],
            (int) $row['min_stock'],
            (bool) $row['featured'],
            (bool) $row['requires_maintenance'],
            (int) $row['maintenance_days'],
            (string) $row['status'],
            (string) $row['created_at'],
            (string) $row['updated_at']
        );
    }

    /**
     * @param mixed $value
     */
    private function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
