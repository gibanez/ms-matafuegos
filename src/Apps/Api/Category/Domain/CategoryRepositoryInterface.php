<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Domain;

interface CategoryRepositoryInterface
{
    public function findById(string $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    /**
     * @return Category[]
     */
    public function findAll(bool $activeOnly = false): array;

    public function existsBySlug(string $slug, ?string $excludeId = null): bool;

    public function save(Category $category): void;

    public function delete(string $id): void;
}
