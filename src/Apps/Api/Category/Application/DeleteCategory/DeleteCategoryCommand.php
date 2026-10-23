<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\DeleteCategory;

final class DeleteCategoryCommand
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
