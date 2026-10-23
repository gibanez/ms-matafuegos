<?php

declare(strict_types=1);

use Gaby\Apps\Api\Migration\Application\GetMigrationStatus\GetMigrationStatusHandler;
use Gaby\Apps\Api\Migration\Application\GetMigrationStatus\GetMigrationStatusQuery;
use Gaby\Apps\Api\Migration\Application\RunMigrations\RunMigrationsCommand;
use Gaby\Apps\Api\Migration\Application\RunMigrations\RunMigrationsHandler;
use Gaby\Tests\Apps\Api\Migration\Doubles\InMemoryMigrationRunner;

test('devuelve el estado de las migraciones', function (): void {
    $status = [
        'environment' => 'development',
        'database' => 'database.sqlite',
        'total' => 2,
        'applied' => 1,
        'pending' => 1,
        'migrations' => [],
    ];
    $runner = new InMemoryMigrationRunner($status, []);
    $handler = new GetMigrationStatusHandler($runner);

    expect($handler->handle(new GetMigrationStatusQuery()))->toBe($status);
});

test('ejecuta las migraciones pendientes', function (): void {
    $result = [
        'applied' => [
            ['version' => '20261022000002', 'name' => 'CreateMigrationSmokeTable', 'status' => 'applied', 'appliedAt' => null],
        ],
        'count' => 1,
        'output' => 'migrated',
        'status' => [
            'environment' => 'development',
            'database' => 'database.sqlite',
            'total' => 2,
            'applied' => 2,
            'pending' => 0,
            'migrations' => [],
        ],
    ];
    $runner = new InMemoryMigrationRunner([], $result);
    $handler = new RunMigrationsHandler($runner);

    expect($handler->handle(new RunMigrationsCommand()))->toBe($result);
    expect($runner->migrateCalls)->toBe(1);
});
