<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\ListCategories;

final class ListCategoriesQuery
{
    /** @var bool */
    public $activeOnly;

    public function __construct(bool $activeOnly = false)
    {
        $this->activeOnly = $activeOnly;
    }
}
