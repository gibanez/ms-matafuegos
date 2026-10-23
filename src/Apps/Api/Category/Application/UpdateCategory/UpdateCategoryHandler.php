<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\UpdateCategory;

use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Category\Domain\Exception\CategoryNotFoundException;
use Gaby\Apps\Api\Category\Domain\Exception\CategorySlugAlreadyInUseException;

final class UpdateCategoryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    public function handle(UpdateCategoryCommand $command): Category
    {
        $category = $this->categories->findById($command->id);

        if ($category === null) {
            throw CategoryNotFoundException::withId($command->id);
        }

        if ($this->categories->existsBySlug($command->slug, $command->id)) {
            throw CategorySlugAlreadyInUseException::withSlug($command->slug);
        }

        $category->updateDetails(
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
