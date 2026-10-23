<?php

declare(strict_types=1);

namespace Gaby\Tests\Apps\Api\Migration\Doubles;

use Gaby\Apps\Api\Migration\Domain\MigrationRunnerInterface;

final class InMemoryMigrationRunner implements MigrationRunnerInterface
{
    /** @var array<string, mixed> */
    private $status;

    /** @var array<string, mixed> */
    private $result;

    /** @var int */
    public $migrateCalls = 0;

    /**
     * @param array<string, mixed> $status
     * @param array<string, mixed> $result
     */
    public function __construct(array $status, array $result)
    {
        $this->status = $status;
        $this->result = $result;
    }

    public function status(): array
    {
        return $this->status;
    }

    public function migrate(): array
    {
        $this->migrateCalls++;

        return $this->result;
    }
}
