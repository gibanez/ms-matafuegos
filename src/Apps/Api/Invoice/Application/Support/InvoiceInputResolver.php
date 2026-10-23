<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\Support;

use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\InvoiceAdjustment;
use Gaby\Apps\Api\Invoice\Domain\InvoiceLine;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;

final class InvoiceInputResolver
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return InvoiceLine[]
     */
    public function resolveLines(array $lines): array
    {
        $resolved = [];

        foreach ($lines as $line) {
            $productId = isset($line['productId']) && $line['productId'] !== null && $line['productId'] !== ''
                ? (string) $line['productId']
                : null;
            $description = isset($line['description']) ? trim((string) $line['description']) : '';
            $quantity = (int) ($line['quantity'] ?? 0);
            $unitPriceCents = isset($line['unitPriceCents']) && $line['unitPriceCents'] !== null
                ? (int) $line['unitPriceCents']
                : null;

            if ($productId !== null) {
                $product = $this->products->findById($productId);

                if ($product === null) {
                    throw InvalidInvoiceException::productNotFound($productId);
                }

                if ($description === '') {
                    $description = $product->name();
                }

                if ($unitPriceCents === null) {
                    $unitPriceCents = $product->priceCents();
                }
            }

            if ($description === '') {
                throw InvalidInvoiceException::invalidLine();
            }

            if ($unitPriceCents === null) {
                throw InvalidInvoiceException::invalidPrice();
            }

            $resolved[] = InvoiceLine::create($productId, $description, $quantity, $unitPriceCents);
        }

        return $resolved;
    }

    /**
     * @param array<int, array<string, mixed>> $adjustments
     * @return InvoiceAdjustment[]
     */
    public function resolveAdjustments(array $adjustments): array
    {
        $resolved = [];

        foreach ($adjustments as $adjustment) {
            $resolved[] = InvoiceAdjustment::create(
                (string) ($adjustment['description'] ?? ''),
                (string) ($adjustment['kind'] ?? ''),
                (string) ($adjustment['valueType'] ?? ''),
                (int) ($adjustment['value'] ?? 0)
            );
        }

        return $resolved;
    }
}
