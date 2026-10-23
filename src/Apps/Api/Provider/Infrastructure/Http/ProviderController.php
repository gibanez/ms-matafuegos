<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Infrastructure\Http;

use Gaby\Apps\Api\Provider\Application\CreateProvider\CreateProviderCommand;
use Gaby\Apps\Api\Provider\Application\CreateProvider\CreateProviderHandler;
use Gaby\Apps\Api\Provider\Application\DeleteProvider\DeleteProviderCommand;
use Gaby\Apps\Api\Provider\Application\DeleteProvider\DeleteProviderHandler;
use Gaby\Apps\Api\Provider\Application\GetProvider\GetProviderHandler;
use Gaby\Apps\Api\Provider\Application\GetProvider\GetProviderQuery;
use Gaby\Apps\Api\Provider\Application\ListProviders\ListProvidersHandler;
use Gaby\Apps\Api\Provider\Application\ListProviders\ListProvidersQuery;
use Gaby\Apps\Api\Provider\Application\UpdateProvider\UpdateProviderCommand;
use Gaby\Apps\Api\Provider\Application\UpdateProvider\UpdateProviderHandler;
use Gaby\Apps\Api\Provider\Domain\Exception\ProviderNotFoundException;
use Gaby\Apps\Api\Provider\Domain\Provider;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ProviderController
{
    use JsonResponseTrait;

    /** @var CreateProviderHandler */
    private $createProvider;

    /** @var ListProvidersHandler */
    private $listProviders;

    /** @var GetProviderHandler */
    private $getProvider;

    /** @var UpdateProviderHandler */
    private $updateProvider;

    /** @var DeleteProviderHandler */
    private $deleteProvider;

    public function __construct(
        CreateProviderHandler $createProvider,
        ListProvidersHandler $listProviders,
        GetProviderHandler $getProvider,
        UpdateProviderHandler $updateProvider,
        DeleteProviderHandler $deleteProvider
    ) {
        $this->createProvider = $createProvider;
        $this->listProviders = $listProviders;
        $this->getProvider = $getProvider;
        $this->updateProvider = $updateProvider;
        $this->deleteProvider = $deleteProvider;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $activeOnly = isset($params['active']) && $this->toBool($params['active']);
        $providers = $this->listProviders->handle(new ListProvidersQuery($activeOnly));

        return $this->json($response, [
            'providers' => array_map(function (Provider $provider): array {
                return $provider->toArray();
            }, $providers),
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $provider = $this->getProvider->handle(new GetProviderQuery((string) $args['id']));
        } catch (ProviderNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $provider->toArray());
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $email = $this->nullable($data, 'email');

        if ($name === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->json($response, ['error' => 'Invalid email address.'], 422);
        }

        $provider = $this->createProvider->handle(new CreateProviderCommand(
            $name,
            $this->nullable($data, 'taxId'),
            $this->nullable($data, 'contactName'),
            $email,
            $this->nullable($data, 'phone'),
            $this->nullable($data, 'address'),
            $this->nullable($data, 'notes'),
            $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Provider::STATUS_ACTIVE)
        ));

        return $this->json($response, $provider->toArray(), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $email = $this->nullable($data, 'email');

        if ($name === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->json($response, ['error' => 'Invalid email address.'], 422);
        }

        try {
            $provider = $this->updateProvider->handle(new UpdateProviderCommand(
                (string) $args['id'],
                $name,
                $this->nullable($data, 'taxId'),
                $this->nullable($data, 'contactName'),
                $email,
                $this->nullable($data, 'phone'),
                $this->nullable($data, 'address'),
                $this->nullable($data, 'notes'),
                $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Provider::STATUS_ACTIVE)
            ));
        } catch (ProviderNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $provider->toArray());
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteProvider->handle(new DeleteProviderCommand((string) $args['id']));
        } catch (ProviderNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullable(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }

        $value = trim((string) $data[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * @param mixed $value
     */
    private function toBool($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'yes'], true);
    }

    private function normalizeStatus(string $status): string
    {
        return $status === Provider::STATUS_INACTIVE ? Provider::STATUS_INACTIVE : Provider::STATUS_ACTIVE;
    }
}
