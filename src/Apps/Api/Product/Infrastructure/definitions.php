<?php

declare(strict_types=1);

use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;
use Gaby\Apps\Api\Product\Infrastructure\Persistence\SqliteProductRepository;
use function DI\autowire;

return [
    ProductRepositoryInterface::class => autowire(SqliteProductRepository::class),
];
