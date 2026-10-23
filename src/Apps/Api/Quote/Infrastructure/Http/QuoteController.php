<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Infrastructure\Http;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Quote\Application\ChangeQuoteStatus\ChangeQuoteStatusCommand;
use Gaby\Apps\Api\Quote\Application\ChangeQuoteStatus\ChangeQuoteStatusHandler;
use Gaby\Apps\Api\Quote\Application\CreateQuote\CreateQuoteCommand;
use Gaby\Apps\Api\Quote\Application\CreateQuote\CreateQuoteHandler;
use Gaby\Apps\Api\Quote\Application\DeleteQuote\DeleteQuoteCommand;
use Gaby\Apps\Api\Quote\Application\DeleteQuote\DeleteQuoteHandler;
use Gaby\Apps\Api\Quote\Application\GetQuote\GetQuoteHandler;
use Gaby\Apps\Api\Quote\Application\GetQuote\GetQuoteQuery;
use Gaby\Apps\Api\Quote\Application\ListQuotes\ListQuotesHandler;
use Gaby\Apps\Api\Quote\Application\ListQuotes\ListQuotesQuery;
use Gaby\Apps\Api\Quote\Application\UpdateQuote\UpdateQuoteCommand;
use Gaby\Apps\Api\Quote\Application\UpdateQuote\UpdateQuoteHandler;
use Gaby\Apps\Api\Quote\Domain\Exception\InvalidQuoteException;
use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotEditableException;
use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotFoundException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Shared\Domain\Money;
use Gaby\Shared\Http\JsonResponseTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class QuoteController
{
    use JsonResponseTrait;

    /** @var CreateQuoteHandler */
    private $createQuote;

    /** @var ListQuotesHandler */
    private $listQuotes;

    /** @var GetQuoteHandler */
    private $getQuote;

    /** @var UpdateQuoteHandler */
    private $updateQuote;

    /** @var DeleteQuoteHandler */
    private $deleteQuote;

    /** @var ChangeQuoteStatusHandler */
    private $changeStatus;

    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(
        CreateQuoteHandler $createQuote,
        ListQuotesHandler $listQuotes,
        GetQuoteHandler $getQuote,
        UpdateQuoteHandler $updateQuote,
        DeleteQuoteHandler $deleteQuote,
        ChangeQuoteStatusHandler $changeStatus,
        CustomerRepositoryInterface $customers
    ) {
        $this->createQuote = $createQuote;
        $this->listQuotes = $listQuotes;
        $this->getQuote = $getQuote;
        $this->updateQuote = $updateQuote;
        $this->deleteQuote = $deleteQuote;
        $this->changeStatus = $changeStatus;
        $this->customers = $customers;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $filters = [];

        foreach (['status', 'customerId', 'from', 'to'] as $key) {
            if (isset($params[$key]) && trim((string) $params[$key]) !== '') {
                $filters[$key] = trim((string) $params[$key]);
            }
        }

        $quotes = $this->listQuotes->handle(new ListQuotesQuery($filters));

        return $this->json($response, [
            'quotes' => array_map(function (Quote $quote): array {
                return $this->present($quote);
            }, $quotes),
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $quote = $this->getQuote->handle(new GetQuoteQuery((string) $args['id']));
        } catch (QuoteNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $this->present($quote));
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
            $quote = $this->createQuote->handle(new CreateQuoteCommand(
                $customerId,
                isset($data['issueDate']) && trim((string) $data['issueDate']) !== ''
                    ? trim((string) $data['issueDate'])
                    : date('Y-m-d'),
                $this->nullable($data, 'validUntil'),
                $this->nullable($data, 'notes'),
                isset($data['status']) ? trim((string) $data['status']) : Quote::STATUS_DRAFT,
                $lines,
                $adjustments
            ));
        } catch (InvalidQuoteException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($quote), 201);
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
            $quote = $this->updateQuote->handle(new UpdateQuoteCommand(
                (string) $args['id'],
                $customerId,
                isset($data['issueDate']) && trim((string) $data['issueDate']) !== ''
                    ? trim((string) $data['issueDate'])
                    : date('Y-m-d'),
                $this->nullable($data, 'validUntil'),
                $this->nullable($data, 'notes'),
                $lines,
                $adjustments
            ));
        } catch (QuoteNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (QuoteNotEditableException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidQuoteException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($quote));
    }

    public function changeStatus(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $status = isset($data['status']) ? trim((string) $data['status']) : '';

        if ($status === '') {
            return $this->json($response, ['error' => 'status is required.'], 422);
        }

        try {
            $quote = $this->changeStatus->handle(
                new ChangeQuoteStatusCommand((string) $args['id'], $status)
            );
        } catch (QuoteNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (QuoteNotEditableException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidQuoteException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($quote));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteQuote->handle(new DeleteQuoteCommand((string) $args['id']));
        } catch (QuoteNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Quote $quote): array
    {
        $data = $quote->toArray();
        $customer = $this->customers->findById($quote->customerId());

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
