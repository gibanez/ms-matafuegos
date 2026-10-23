<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Domain;

interface MigrationRunnerInterface
{
    /**
     * @return array{
     *     environment: string,
     *     database: string,
     *     total: int,
     *     applied: int,
     *     pending: int,
     *     migrations: array<int, array{
     *         version: string,
     *         name: string,
     *         status: string,
     *         appliedAt: string|null
     *     }>
     * }
     */
    public function status(): array;

    /**
     * @return array{
     *     applied: array<int, array{
     *         version: string,
     *         name: string,
     *         status: string,
     *         appliedAt: string|null
     *     }>,
     *     count: int,
     *     output: string,
     *     status: array<string, mixed>
     * }
     */
    public function migrate(): array;
}
