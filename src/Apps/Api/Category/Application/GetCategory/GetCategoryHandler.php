<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\GetCategory;

use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Category\Domain\Exception\CategoryNotFoundException;

final class GetCategoryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    public function handle(GetCategoryQuery $query): Category
    {
        $category = $this->categories->findById($query->id);

        if ($category === null) {
            throw CategoryNotFoundException::withId($query->id);
        }

        return $category;
    }
}
