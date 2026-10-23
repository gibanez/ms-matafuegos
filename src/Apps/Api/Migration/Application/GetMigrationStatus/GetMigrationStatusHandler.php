<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Application\GetMigrationStatus;

use Gaby\Apps\Api\Migration\Domain\MigrationRunnerInterface;

final class GetMigrationStatusHandler
{
    /** @var MigrationRunnerInterface */
    private $runner;

    public function __construct(MigrationRunnerInterface $runner)
    {
        $this->runner = $runner;
    }

    public function handle(GetMigrationStatusQuery $query): array
    {
        return $this->runner->status();
    }
}
