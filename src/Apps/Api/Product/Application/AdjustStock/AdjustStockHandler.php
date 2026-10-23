<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\AdjustStock;

use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class AdjustStockHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    public function handle(AdjustStockCommand $command): Product
    {
        if ($this->products->findById($command->id) === null) {
            throw ProductNotFoundException::withId($command->id);
        }

        $this->products->adjustStock([$command->id => $command->delta]);

        $product = $this->products->findById($command->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($command->id);
        }

        return $product;
    }
}
