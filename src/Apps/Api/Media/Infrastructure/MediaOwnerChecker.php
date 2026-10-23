<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Infrastructure;

use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\OwnerExistenceCheckerInterface;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class MediaOwnerChecker implements OwnerExistenceCheckerInterface
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    public function exists(string $ownerType, string $ownerId): bool
    {
        if ($ownerType === Media::OWNER_PRODUCT) {
            return $this->products->findById($ownerId) !== null;
        }

        return false;
    }
}
