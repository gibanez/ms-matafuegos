<?php

declare(strict_types=1);

use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\MediaStorageInterface;
use Gaby\Apps\Api\Media\Domain\OwnerExistenceCheckerInterface;
use Gaby\Apps\Api\Media\Infrastructure\MediaOwnerChecker;
use Gaby\Apps\Api\Media\Infrastructure\Persistence\SqliteMediaRepository;
use Gaby\Apps\Api\Media\Infrastructure\Storage\ProductMediaStorage;
use function DI\autowire;

return [
    MediaRepositoryInterface::class => autowire(SqliteMediaRepository::class),
    MediaStorageInterface::class => autowire(ProductMediaStorage::class),
    OwnerExistenceCheckerInterface::class => autowire(MediaOwnerChecker::class),
];
