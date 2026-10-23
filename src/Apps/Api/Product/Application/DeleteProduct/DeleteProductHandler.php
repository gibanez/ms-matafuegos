<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\DeleteProduct;

use Gaby\Apps\Api\Media\Application\DeleteOwnerMedia\DeleteOwnerMediaCommand;
use Gaby\Apps\Api\Media\Application\DeleteOwnerMedia\DeleteOwnerMediaHandler;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class DeleteProductHandler
{
    /** @var ProductRepositoryInterface */
    private $products;

    /** @var DeleteOwnerMediaHandler */
    private $deleteMedia;

    public function __construct(ProductRepositoryInterface $products, DeleteOwnerMediaHandler $deleteMedia)
    {
        $this->products = $products;
        $this->deleteMedia = $deleteMedia;
    }

    public function handle(DeleteProductCommand $command): void
    {
        $product = $this->products->findById($command->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($command->id);
        }

        $this->deleteMedia->handle(new DeleteOwnerMediaCommand(Media::OWNER_PRODUCT, $command->id));
        $this->products->delete($command->id);
    }
}
