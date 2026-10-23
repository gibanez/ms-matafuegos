<?php

declare(strict_types=1);

use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;
use Gaby\Apps\Api\Provider\Infrastructure\Persistence\SqliteProviderRepository;
use function DI\autowire;

return [
    ProviderRepositoryInterface::class => autowire(SqliteProviderRepository::class),
];
