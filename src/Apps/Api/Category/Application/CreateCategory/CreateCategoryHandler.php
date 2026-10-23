<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\CreateCategory;

use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Category\Domain\Exception\CategorySlugAlreadyInUseException;

final class CreateCategoryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    public function handle(CreateCategoryCommand $command): Category
    {
        if ($this->categories->existsBySlug($command->slug)) {
            throw CategorySlugAlreadyInUseException::withSlug($command->slug);
        }

        $category = Category::create(
            $command->name,
            $command->slug,
            $command->description,
            $command->status,
            date('c')
        );

        $this->categories->save($category);

        return $category;
    }
}
