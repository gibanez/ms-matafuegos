<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Infrastructure\Persistence;

use Gaby\Apps\Api\Migration\Domain\Exception\MigrationAlreadyRunningException;
use Gaby\Apps\Api\Migration\Domain\Exception\MigrationFailedException;
use Gaby\Apps\Api\Migration\Domain\MigrationRunnerInterface;
use Gaby\Shared\Config\Env;
use Phinx\Config\Config;
use Phinx\Migration\Manager;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

final class PhinxMigrationRunner implements MigrationRunnerInterface
{
    private const LOCK_FILE = 'storage/migrations.lock';

    public function status(): array
    {
        $manager = $this->createManager(new BufferedOutput());

        return $this->buildStatus($manager);
    }

    public function migrate(): array
    {
        $lock = $this->acquireLock();

        try {
            $output = new BufferedOutput();

            try {
                $manager = $this->createManager($output);
                $before = $this->buildStatus($manager);

                $manager->migrate($this->environment($manager));

                $after = $this->buildStatus($manager);
            } catch (MigrationFailedException $error) {
                throw $error;
            } catch (Throwable $error) {
                throw MigrationFailedException::create($error->getMessage(), trim($output->fetch()));
            }

            $beforeVersions = [];

            foreach ($before['migrations'] as $migration) {
                if ($migration['status'] === 'applied') {
                    $beforeVersions[] = $migration['version'];
                }
            }

            $applied = array_values(array_filter(
                $after['migrations'],
                function (array $migration) use ($beforeVersions): bool {
                    return !in_array($migration['version'], $beforeVersions, true);
                }
            ));

            return [
                'applied' => $applied,
                'count' => count($applied),
                'output' => trim($output->fetch()),
                'status' => $after,
            ];
        } finally {
            $this->releaseLock($lock);
        }
    }

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
    private function buildStatus(Manager $manager): array
    {
        $environment = $this->environment($manager);
        $migrations = $manager->getMigrations($environment);
        $versionLog = $manager->getEnvironment($environment)->getVersionLog();
        $items = [];
        $applied = 0;

        foreach ($migrations as $version => $migration) {
            $entry = $versionLog[$version] ?? null;
            $isApplied = $entry !== null;

            if ($isApplied) {
                $applied++;
            }

            $appliedAt = null;

            if ($entry !== null) {
                $value = $entry['end_time'] ?? $entry['start_time'] ?? null;
                $appliedAt = is_string($value) && $value !== '' ? $value : null;
            }

            $items[] = [
                'version' => (string) $version,
                'name' => $migration->getName(),
                'status' => $isApplied ? 'applied' : 'pending',
                'appliedAt' => $appliedAt,
            ];
        }

        return [
            'environment' => $environment,
            'database' => $this->databaseName(),
            'total' => count($items),
            'applied' => $applied,
            'pending' => count($items) - $applied,
            'migrations' => $items,
        ];
    }

    private function createManager(BufferedOutput $output): Manager
    {
        $configFile = Env::rootPath() . DIRECTORY_SEPARATOR . 'phinx.php';
        $configArray = require $configFile;

        if (!is_array($configArray)) {
            throw new RuntimeException('Invalid Phinx configuration.');
        }

        return new Manager(new Config($configArray), new ArrayInput([]), $output);
    }

    private function environment(Manager $manager): string
    {
        return $manager->getConfig()->getDefaultEnvironment();
    }

    private function databaseName(): string
    {
        $path = (string) Env::get('DB_PATH', 'storage/database.sqlite');
        $path = str_replace('\\', '/', $path);

        return basename($path === '' ? 'database.sqlite' : $path);
    }

    /**
     * @return resource
     */
    private function acquireLock()
    {
        $path = Env::rootPath() . DIRECTORY_SEPARATOR . self::LOCK_FILE;
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the storage directory.');
        }

        $handle = @fopen($path, 'c');

        if ($handle === false) {
            throw new RuntimeException('Could not create the migration lock file.');
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            throw MigrationAlreadyRunningException::create();
        }

        return $handle;
    }

    /**
     * @param resource $handle
     */
    private function releaseLock($handle): void
    {
        if (!is_resource($handle)) {
            return;
        }

        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
