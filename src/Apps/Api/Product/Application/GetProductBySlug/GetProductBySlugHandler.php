<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\GetProductBySlug;

use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class GetProductBySlugHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    public function handle(GetProductBySlugQuery $query): Product
    {
        $product = $this->products->findBySlug($query->slug);

        if ($product === null || ($query->activeOnly && !$product->isActive())) {
            throw ProductNotFoundException::withSlug($query->slug);
        }

        return $product;
    }
}
