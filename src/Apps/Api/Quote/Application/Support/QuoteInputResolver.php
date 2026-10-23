<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\Support;

use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;
use Gaby\Apps\Api\Quote\Domain\Exception\InvalidQuoteException;
use Gaby\Apps\Api\Quote\Domain\QuoteAdjustment;
use Gaby\Apps\Api\Quote\Domain\QuoteLine;

final class QuoteInputResolver
{
    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(ProductRepositoryInterface $products)
    {
        $this->products = $products;
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return QuoteLine[]
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
                    throw InvalidQuoteException::productNotFound($productId);
                }

                if ($description === '') {
                    $description = $product->name();
                }

                if ($unitPriceCents === null) {
                    $unitPriceCents = $product->priceCents();
                }
            }

            if ($description === '') {
                throw InvalidQuoteException::invalidLine();
            }

            if ($unitPriceCents === null) {
                throw InvalidQuoteException::invalidPrice();
            }

            $resolved[] = QuoteLine::create($productId, $description, $quantity, $unitPriceCents);
        }

        return $resolved;
    }

    /**
     * @param array<int, array<string, mixed>> $adjustments
     * @return QuoteAdjustment[]
     */
    public function resolveAdjustments(array $adjustments): array
    {
        $resolved = [];

        foreach ($adjustments as $adjustment) {
            $resolved[] = QuoteAdjustment::create(
                (string) ($adjustment['description'] ?? ''),
                (string) ($adjustment['kind'] ?? ''),
                (string) ($adjustment['valueType'] ?? ''),
                (int) ($adjustment['value'] ?? 0)
            );
        }

        return $resolved;
    }
}
