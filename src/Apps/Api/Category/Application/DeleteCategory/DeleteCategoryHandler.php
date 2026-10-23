<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\DeleteCategory;

use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Category\Domain\Exception\CategoryNotFoundException;

final class DeleteCategoryHandler
{
    /** @var CategoryRepositoryInterface */
    private $categories;

    public function __construct(CategoryRepositoryInterface $categories)
    {
        $this->categories = $categories;
    }

    public function handle(DeleteCategoryCommand $command): void
    {
        $category = $this->categories->findById($command->id);

        if ($category === null) {
            throw CategoryNotFoundException::withId($command->id);
        }

        $this->categories->delete($command->id);
    }
}
