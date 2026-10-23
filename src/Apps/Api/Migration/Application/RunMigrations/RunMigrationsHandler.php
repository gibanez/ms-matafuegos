<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Application\RunMigrations;

use Gaby\Apps\Api\Migration\Domain\MigrationRunnerInterface;

final class RunMigrationsHandler
{
    /** @var MigrationRunnerInterface */
    private $runner;

    public function __construct(MigrationRunnerInterface $runner)
    {
        $this->runner = $runner;
    }

    public function handle(RunMigrationsCommand $command): array
    {
        return $this->runner->migrate();
    }
}
