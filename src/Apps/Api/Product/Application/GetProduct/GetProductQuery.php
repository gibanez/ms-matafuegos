<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\GetProduct;

final class GetProductQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
