<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Infrastructure\Http;

use Gaby\Apps\Api\Migration\Application\GetMigrationStatus\GetMigrationStatusHandler;
use Gaby\Apps\Api\Migration\Application\GetMigrationStatus\GetMigrationStatusQuery;
use Gaby\Apps\Api\Migration\Application\RunMigrations\RunMigrationsCommand;
use Gaby\Apps\Api\Migration\Application\RunMigrations\RunMigrationsHandler;
use Gaby\Apps\Api\Migration\Domain\Exception\MigrationAlreadyRunningException;
use Gaby\Apps\Api\Migration\Domain\Exception\MigrationFailedException;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class MigrationController
{
    use JsonResponseTrait;

    /** @var GetMigrationStatusHandler */
    private $statusHandler;

    /** @var RunMigrationsHandler */
    private $runHandler;

    public function __construct(
        GetMigrationStatusHandler $statusHandler,
        RunMigrationsHandler $runHandler
    ) {
        $this->statusHandler = $statusHandler;
        $this->runHandler = $runHandler;
    }

    public function status(Request $request, Response $response): Response
    {
        try {
            $status = $this->statusHandler->handle(new GetMigrationStatusQuery());
        } catch (Throwable $error) {
            return $this->json($response, [
                'error' => 'Could not read migration status.',
                'detail' => $error->getMessage(),
            ], 500);
        }

        return $this->json($response, $status);
    }

    public function run(Request $request, Response $response): Response
    {
        try {
            $result = $this->runHandler->handle(new RunMigrationsCommand());
        } catch (MigrationAlreadyRunningException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (MigrationFailedException $error) {
            return $this->json($response, [
                'error' => $error->getMessage(),
                'detail' => $error->detail(),
                'output' => $error->output(),
            ], 500);
        } catch (Throwable $error) {
            return $this->json($response, [
                'error' => 'Could not run migrations.',
                'detail' => $error->getMessage(),
            ], 500);
        }

        return $this->json($response, $result);
    }
}
