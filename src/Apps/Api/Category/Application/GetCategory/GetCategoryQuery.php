<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Application\GetCategory;

final class GetCategoryQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
