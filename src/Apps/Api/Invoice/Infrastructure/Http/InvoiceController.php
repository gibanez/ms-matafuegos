<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Infrastructure\Http;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus\ChangeInvoiceStatusCommand;
use Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus\ChangeInvoiceStatusHandler;
use Gaby\Apps\Api\Invoice\Application\CreateInvoice\CreateInvoiceCommand;
use Gaby\Apps\Api\Invoice\Application\CreateInvoice\CreateInvoiceHandler;
use Gaby\Apps\Api\Invoice\Application\CreateInvoiceFromQuote\CreateInvoiceFromQuoteCommand;
use Gaby\Apps\Api\Invoice\Application\CreateInvoiceFromQuote\CreateInvoiceFromQuoteHandler;
use Gaby\Apps\Api\Invoice\Application\DeleteInvoice\DeleteInvoiceCommand;
use Gaby\Apps\Api\Invoice\Application\DeleteInvoice\DeleteInvoiceHandler;
use Gaby\Apps\Api\Invoice\Application\GetInvoice\GetInvoiceHandler;
use Gaby\Apps\Api\Invoice\Application\GetInvoice\GetInvoiceQuery;
use Gaby\Apps\Api\Invoice\Application\ListInvoices\ListInvoicesHandler;
use Gaby\Apps\Api\Invoice\Application\ListInvoices\ListInvoicesQuery;
use Gaby\Apps\Api\Invoice\Application\UpdateInvoice\UpdateInvoiceCommand;
use Gaby\Apps\Api\Invoice\Application\UpdateInvoice\UpdateInvoiceHandler;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotEditableException;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotFoundException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Shared\Domain\Money;
use Gaby\Shared\Http\JsonResponseTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class InvoiceController
{
    use JsonResponseTrait;

    /** @var CreateInvoiceHandler */
    private $createInvoice;

    /** @var ListInvoicesHandler */
    private $listInvoices;

    /** @var GetInvoiceHandler */
    private $getInvoice;

    /** @var UpdateInvoiceHandler */
    private $updateInvoice;

    /** @var DeleteInvoiceHandler */
    private $deleteInvoice;

    /** @var ChangeInvoiceStatusHandler */
    private $changeStatus;

    /** @var CreateInvoiceFromQuoteHandler */
    private $createFromQuote;

    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(
        CreateInvoiceHandler $createInvoice,
        ListInvoicesHandler $listInvoices,
        GetInvoiceHandler $getInvoice,
        UpdateInvoiceHandler $updateInvoice,
        DeleteInvoiceHandler $deleteInvoice,
        ChangeInvoiceStatusHandler $changeStatus,
        CreateInvoiceFromQuoteHandler $createFromQuote,
        CustomerRepositoryInterface $customers
    ) {
        $this->createInvoice = $createInvoice;
        $this->listInvoices = $listInvoices;
        $this->getInvoice = $getInvoice;
        $this->updateInvoice = $updateInvoice;
        $this->deleteInvoice = $deleteInvoice;
        $this->changeStatus = $changeStatus;
        $this->createFromQuote = $createFromQuote;
        $this->customers = $customers;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $filters = [];

        foreach (['status', 'customerId', 'quoteId', 'from', 'to'] as $key) {
            if (isset($params[$key]) && trim((string) $params[$key]) !== '') {
                $filters[$key] = trim((string) $params[$key]);
            }
        }

        $invoices = $this->listInvoices->handle(new ListInvoicesQuery($filters));

        return $this->json($response, [
            'invoices' => array_map(function (Invoice $invoice): array {
                return $this->present($invoice);
            }, $invoices),
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $invoice = $this->getInvoice->handle(new GetInvoiceQuery((string) $args['id']));
        } catch (InvoiceNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $this->present($invoice));
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $customerId = isset($data['customerId']) ? trim((string) $data['customerId']) : '';

        if ($customerId === '') {
            return $this->json($response, ['error' => 'customerId is required.'], 422);
        }

        if (!isset($data['lines']) || !is_array($data['lines']) || $data['lines'] === []) {
            return $this->json($response, ['error' => 'lines must contain at least one item.'], 422);
        }

        try {
            $lines = $this->parseLines($data['lines']);
            $adjustments = $this->parseAdjustments(isset($data['adjustments']) && is_array($data['adjustments']) ? $data['adjustments'] : []);
        } catch (InvalidArgumentException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        try {
            $invoice = $this->createInvoice->handle(new CreateInvoiceCommand(
                $customerId,
                isset($data['issueDate']) && trim((string) $data['issueDate']) !== ''
                    ? trim((string) $data['issueDate'])
                    : date('Y-m-d'),
                $this->nullable($data, 'dueDate'),
                $this->nullable($data, 'paymentMethod'),
                $this->nullable($data, 'notes'),
                $lines,
                $adjustments
            ));
        } catch (InvalidInvoiceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($invoice), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $customerId = isset($data['customerId']) ? trim((string) $data['customerId']) : '';

        if ($customerId === '') {
            return $this->json($response, ['error' => 'customerId is required.'], 422);
        }

        if (!isset($data['lines']) || !is_array($data['lines']) || $data['lines'] === []) {
            return $this->json($response, ['error' => 'lines must contain at least one item.'], 422);
        }

        try {
            $lines = $this->parseLines($data['lines']);
            $adjustments = $this->parseAdjustments(isset($data['adjustments']) && is_array($data['adjustments']) ? $data['adjustments'] : []);
        } catch (InvalidArgumentException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        try {
            $invoice = $this->updateInvoice->handle(new UpdateInvoiceCommand(
                (string) $args['id'],
                $customerId,
                isset($data['issueDate']) && trim((string) $data['issueDate']) !== ''
                    ? trim((string) $data['issueDate'])
                    : date('Y-m-d'),
                $this->nullable($data, 'dueDate'),
                $this->nullable($data, 'paymentMethod'),
                $this->nullable($data, 'notes'),
                $lines,
                $adjustments
            ));
        } catch (InvoiceNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (InvoiceNotEditableException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidInvoiceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($invoice));
    }

    public function changeStatus(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $status = isset($data['status']) ? trim((string) $data['status']) : '';

        if ($status === '') {
            return $this->json($response, ['error' => 'status is required.'], 422);
        }

        try {
            $invoice = $this->changeStatus->handle(
                new ChangeInvoiceStatusCommand((string) $args['id'], $status)
            );
        } catch (InvoiceNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (InvoiceNotEditableException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidInvoiceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        } catch (InsufficientStockException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $this->present($invoice));
    }

    public function fromQuote(Request $request, Response $response, array $args): Response
    {
        try {
            $invoice = $this->createFromQuote->handle(
                new CreateInvoiceFromQuoteCommand((string) $args['quoteId'])
            );
        } catch (InvalidInvoiceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($invoice), 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteInvoice->handle(new DeleteInvoiceCommand((string) $args['id']));
        } catch (InvoiceNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (InvoiceNotEditableException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $response->withStatus(204);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Invoice $invoice): array
    {
        $data = $invoice->toArray();
        $customer = $this->customers->findById($invoice->customerId());

        if ($customer instanceof Customer) {
            $data['customer'] = [
                'id' => $customer->id(),
                'name' => $customer->name(),
                'taxId' => $customer->taxId(),
                'email' => $customer->email(),
                'phone' => $customer->phone(),
            ];
        }

        return $data;
    }

    /**
     * @param array<int, mixed> $rawLines
     * @return array<int, array<string, mixed>>
     */
    private function parseLines(array $rawLines): array
    {
        $lines = [];

        foreach ($rawLines as $rawLine) {
            if (!is_array($rawLine)) {
                throw new InvalidArgumentException('Each line must be an object.');
            }

            $quantity = filter_var($rawLine['quantity'] ?? null, FILTER_VALIDATE_INT);

            if ($quantity === false || $quantity < 1) {
                throw new InvalidArgumentException('Each line needs a positive quantity.');
            }

            $productId = isset($rawLine['productId']) && trim((string) $rawLine['productId']) !== ''
                ? trim((string) $rawLine['productId'])
                : null;
            $description = isset($rawLine['description']) && trim((string) $rawLine['description']) !== ''
                ? trim((string) $rawLine['description'])
                : null;
            $unitPriceCents = null;

            if (array_key_exists('unitPrice', $rawLine) && $rawLine['unitPrice'] !== null && $rawLine['unitPrice'] !== '') {
                try {
                    $unitPriceCents = Money::toCents($rawLine['unitPrice']);
                } catch (InvalidArgumentException $error) {
                    throw new InvalidArgumentException('Invalid unitPrice.');
                }
            }

            if ($unitPriceCents !== null && $unitPriceCents < 0) {
                throw new InvalidArgumentException('unitPrice cannot be negative.');
            }

            if ($productId === null && ($description === null || $unitPriceCents === null)) {
                throw new InvalidArgumentException('Custom lines need a description and a unitPrice.');
            }

            $lines[] = [
                'productId' => $productId,
                'description' => $description,
                'quantity' => (int) $quantity,
                'unitPriceCents' => $unitPriceCents,
            ];
        }

        return $lines;
    }

    /**
     * @param array<int, mixed> $rawAdjustments
     * @return array<int, array<string, mixed>>
     */
    private function parseAdjustments(array $rawAdjustments): array
    {
        $adjustments = [];

        foreach ($rawAdjustments as $rawAdjustment) {
            if (!is_array($rawAdjustment)) {
                throw new InvalidArgumentException('Each adjustment must be an object.');
            }

            $description = isset($rawAdjustment['description']) ? trim((string) $rawAdjustment['description']) : '';
            $kind = isset($rawAdjustment['kind']) ? trim((string) $rawAdjustment['kind']) : '';
            $valueType = isset($rawAdjustment['valueType']) ? trim((string) $rawAdjustment['valueType']) : '';

            if ($description === '' || $kind === '' || $valueType === '' || !array_key_exists('value', $rawAdjustment)) {
                throw new InvalidArgumentException('Each adjustment needs description, kind, valueType and value.');
            }

            if (!in_array($kind, ['surcharge', 'discount'], true)) {
                throw new InvalidArgumentException('Adjustment kind must be surcharge or discount.');
            }

            if (!in_array($valueType, ['fixed', 'percentage'], true)) {
                throw new InvalidArgumentException('Adjustment valueType must be fixed or percentage.');
            }

            try {
                $value = $valueType === 'fixed'
                    ? Money::toCents($rawAdjustment['value'])
                    : Money::percentageToBasisPoints($rawAdjustment['value']);
            } catch (InvalidArgumentException $error) {
                throw new InvalidArgumentException('Invalid adjustment value.');
            }

            if ($value < 0) {
                throw new InvalidArgumentException('Adjustment value cannot be negative.');
            }

            if ($valueType === 'percentage' && $value > 10000) {
                throw new InvalidArgumentException('Adjustment percentage cannot exceed 100.');
            }

            $adjustments[] = [
                'description' => $description,
                'kind' => $kind,
                'valueType' => $valueType,
                'value' => $value,
            ];
        }

        return $adjustments;
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
}
