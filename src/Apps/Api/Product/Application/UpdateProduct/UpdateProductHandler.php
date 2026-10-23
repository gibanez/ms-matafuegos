<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\UpdateProduct;

use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Product\Domain\Exception\InvalidProductReferenceException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductSlugAlreadyInUseException;
use Gaby\Apps\Api\Product\Domain\Exception\SkuAlreadyInUseException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;

final class UpdateProductHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    /** @var CategoryRepositoryInterface */
    private $categories;

    /** @var ProviderRepositoryInterface */
    private $providers;

    public function __construct(
        ProductRepositoryInterface $products,
        CategoryRepositoryInterface $categories,
        ProviderRepositoryInterface $providers
    ) {
        $this->products = $products;
        $this->categories = $categories;
        $this->providers = $providers;
    }

    public function handle(UpdateProductCommand $command): Product
    {
        $product = $this->products->findById($command->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($command->id);
        }

        if ($this->products->existsBySlug($command->slug, $command->id)) {
            throw ProductSlugAlreadyInUseException::withSlug($command->slug);
        }

        if ($command->sku !== null && $this->products->existsBySku($command->sku, $command->id)) {
            throw SkuAlreadyInUseException::withSku($command->sku);
        }

        if ($command->categoryId !== null && $this->categories->findById($command->categoryId) === null) {
            throw InvalidProductReferenceException::categoryNotFound($command->categoryId);
        }

        if ($command->providerId !== null && $this->providers->findById($command->providerId) === null) {
            throw InvalidProductReferenceException::providerNotFound($command->providerId);
        }

        $product->updateDetails(
            $command->name,
            $command->slug,
            $command->description,
            $command->sku,
            $command->categoryId,
            $command->providerId,
            $command->priceCents,
            $command->showPrice,
            $command->stock,
            $command->minStock,
            $command->featured,
            $command->requiresMaintenance,
            $command->maintenanceDays,
            $command->status,
            date('c')
        );

        $this->products->save($product);

        return $product;
    }
}
