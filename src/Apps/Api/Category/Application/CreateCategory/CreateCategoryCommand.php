<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\CreateCategory;

use Gaby\Apps\Api\Category\Domain\Category;

final class CreateCategoryCommand
{
    /** @var string */
    public $name;

    /** @var string */
    public $slug;

    /** @var string|null */
    public $description;

    /** @var string */
    public $status;

    public function __construct(string $name, string $slug, ?string $description, string $status = Category::STATUS_ACTIVE)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
    }
}
