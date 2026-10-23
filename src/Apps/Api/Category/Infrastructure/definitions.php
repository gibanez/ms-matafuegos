<?php

declare(strict_types=1);

use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Category\Infrastructure\Persistence\SqliteCategoryRepository;
use function DI\autowire;

return [
    CategoryRepositoryInterface::class => autowire(SqliteCategoryRepository::class),
];
