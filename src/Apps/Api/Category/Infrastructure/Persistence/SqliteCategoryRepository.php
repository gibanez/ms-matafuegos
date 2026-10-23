<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Infrastructure\Persistence;

use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use PDO;

final class SqliteCategoryRepository implements CategoryRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Category
    {
        $statement = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function findBySlug(string $slug): ?Category
    {
        $statement = $this->pdo->prepare('SELECT * FROM categories WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @return Category[]
     */
    public function findAll(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM categories';

        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }

        $rows = $this->pdo->query($sql . ' ORDER BY name ASC')->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function save(Category $category): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO categories (id, name, slug, description, status, created_at, updated_at)
             VALUES (:id, :name, :slug, :description, :status, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                slug = excluded.slug,
                description = excluded.description,
                status = excluded.status,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'id' => $category->id(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'description' => $category->description(),
            'status' => $category->status(),
            'created_at' => $category->createdAt(),
            'updated_at' => $category->updatedAt(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Category
    {
        return Category::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            (string) $row['slug'],
            $row['description'] === null ? null : (string) $row['description'],
            (string) $row['status'],
            (string) $row['created_at'],
            (string) $row['updated_at']
        );
    }
}
