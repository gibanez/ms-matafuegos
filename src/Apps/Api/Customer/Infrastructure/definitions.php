<?php

declare(strict_types=1);

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Customer\Infrastructure\Persistence\SqliteCustomerRepository;
use function DI\autowire;

return [
    CustomerRepositoryInterface::class => autowire(SqliteCustomerRepository::class),
];
