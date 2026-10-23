<?php

declare(strict_types=1);

use Gaby\Apps\Api\Maintenance\Domain\MaintenanceRepositoryInterface;
use Gaby\Apps\Api\Maintenance\Infrastructure\Persistence\SqliteMaintenanceRepository;
use function DI\autowire;

return [
    MaintenanceRepositoryInterface::class => autowire(SqliteMaintenanceRepository::class),
];
