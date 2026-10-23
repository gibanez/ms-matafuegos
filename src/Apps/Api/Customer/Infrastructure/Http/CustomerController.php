<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Infrastructure\Http;

use Gaby\Apps\Api\Customer\Application\CreateCustomer\CreateCustomerCommand;
use Gaby\Apps\Api\Customer\Application\CreateCustomer\CreateCustomerHandler;
use Gaby\Apps\Api\Customer\Application\DeleteCustomer\DeleteCustomerCommand;
use Gaby\Apps\Api\Customer\Application\DeleteCustomer\DeleteCustomerHandler;
use Gaby\Apps\Api\Customer\Application\GetCustomer\GetCustomerHandler;
use Gaby\Apps\Api\Customer\Application\GetCustomer\GetCustomerQuery;
use Gaby\Apps\Api\Customer\Application\ListCustomers\ListCustomersHandler;
use Gaby\Apps\Api\Customer\Application\ListCustomers\ListCustomersQuery;
use Gaby\Apps\Api\Customer\Application\UpdateCustomer\UpdateCustomerCommand;
use Gaby\Apps\Api\Customer\Application\UpdateCustomer\UpdateCustomerHandler;
use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerInUseException;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerNotFoundException;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerTaxIdAlreadyInUseException;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CustomerController
{
    use JsonResponseTrait;

    /** @var CreateCustomerHandler */
    private $createCustomer;

    /** @var ListCustomersHandler */
    private $listCustomers;

    /** @var GetCustomerHandler */
    private $getCustomer;

    /** @var UpdateCustomerHandler */
    private $updateCustomer;

    /** @var DeleteCustomerHandler */
    private $deleteCustomer;

    public function __construct(
        CreateCustomerHandler $createCustomer,
        ListCustomersHandler $listCustomers,
        GetCustomerHandler $getCustomer,
        UpdateCustomerHandler $updateCustomer,
        DeleteCustomerHandler $deleteCustomer
    ) {
        $this->createCustomer = $createCustomer;
        $this->listCustomers = $listCustomers;
        $this->getCustomer = $getCustomer;
        $this->updateCustomer = $updateCustomer;
        $this->deleteCustomer = $deleteCustomer;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $q = isset($params['q']) && trim((string) $params['q']) !== '' ? trim((string) $params['q']) : null;
        $customers = $this->listCustomers->handle(new ListCustomersQuery($q));

        return $this->json($response, [
            'customers' => array_map(function (Customer $customer): array {
                return $customer->toArray();
            }, $customers),
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $customer = $this->getCustomer->handle(new GetCustomerQuery((string) $args['id']));
        } catch (CustomerNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $customer->toArray());
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

        try {
            $customer = $this->createCustomer->handle(new CreateCustomerCommand(
                $name,
                $this->nullable($data, 'taxId'),
                $email,
                $this->nullable($data, 'phone'),
                $this->nullable($data, 'address'),
                $this->nullable($data, 'city'),
                $this->nullable($data, 'notes'),
                $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Customer::STATUS_ACTIVE)
            ));
        } catch (CustomerTaxIdAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $customer->toArray(), 201);
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
            $customer = $this->updateCustomer->handle(new UpdateCustomerCommand(
                (string) $args['id'],
                $name,
                $this->nullable($data, 'taxId'),
                $email,
                $this->nullable($data, 'phone'),
                $this->nullable($data, 'address'),
                $this->nullable($data, 'city'),
                $this->nullable($data, 'notes'),
                $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Customer::STATUS_ACTIVE)
            ));
        } catch (CustomerNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (CustomerTaxIdAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $customer->toArray());
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteCustomer->handle(new DeleteCustomerCommand((string) $args['id']));
        } catch (CustomerNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (CustomerInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
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

    private function normalizeStatus(string $status): string
    {
        return $status === Customer::STATUS_INACTIVE ? Customer::STATUS_INACTIVE : Customer::STATUS_ACTIVE;
    }
}
