<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\UpdateCategory;

use Gaby\Apps\Api\Category\Domain\Category;

final class UpdateCategoryCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $slug;

    /** @var string|null */
    public $description;

    /** @var string */
    public $status;

    public function __construct(
        string $id,
        string $name,
        string $slug,
        ?string $description,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
    }
}
