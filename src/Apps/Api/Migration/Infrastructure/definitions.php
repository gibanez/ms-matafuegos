<?php

declare(strict_types=1);

use Gaby\Apps\Api\Migration\Domain\MigrationRunnerInterface;
use Gaby\Apps\Api\Migration\Infrastructure\Persistence\PhinxMigrationRunner;
use function DI\autowire;

return [
    MigrationRunnerInterface::class => autowire(PhinxMigrationRunner::class),
];
