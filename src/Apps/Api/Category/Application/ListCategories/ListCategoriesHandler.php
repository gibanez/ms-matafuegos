<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\ListCategories;

use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;

final class ListCategoriesHandler
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return \Gaby\Apps\Api\Category\Domain\Category[]
     */
    public function handle(ListCategoriesQuery $query): array
    {
        return $this->categories->findAll($query->activeOnly);
    }
}
