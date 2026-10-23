<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\GetProduct;

use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class GetProductHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    public function handle(GetProductQuery $query): Product
    {
        $product = $this->products->findById($query->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($query->id);
        }

        return $product;
    }
}
