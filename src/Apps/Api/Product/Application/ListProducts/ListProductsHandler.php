<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\ListProducts;

use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class ListProductsHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    /**
     * @return \Gaby\Apps\Api\Product\Domain\Product[]
     */
    public function handle(ListProductsQuery $query): array
    {
        return $this->products->findAll($query->filters);
    }
}
