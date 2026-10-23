<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain;

use Gaby\Shared\Domain\Money;
use Ramsey\Uuid\Uuid;

final class QuoteLine
{
    /** @var string */
    private $id;

    /** @var string|null */
    private $productId;

    /** @var string */
    private $description;

    /** @var int */
    private $quantity;

    /** @var int */
    private $unitPriceCents;

    /** @var int */
    private $lineTotalCents;

    private function __construct(
        string $id,
        ?string $productId,
        string $description,
        int $quantity,
        int $unitPriceCents,
        int $lineTotalCents
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPriceCents = $unitPriceCents;
        $this->lineTotalCents = $lineTotalCents;
    }

    public static function create(
        ?string $productId,
        string $description,
        int $quantity,
        int $unitPriceCents
    ): self {
        if (trim($description) === '') {
            throw Exception\InvalidQuoteException::invalidLine();
        }

        if ($quantity < 1) {
            throw Exception\InvalidQuoteException::invalidQuantity();
        }

        if ($unitPriceCents < 0) {
            throw Exception\InvalidQuoteException::invalidPrice();
        }

        return new self(
            Uuid::uuid4()->toString(),
            $productId,
            trim($description),
            $quantity,
            $unitPriceCents,
            $quantity * $unitPriceCents
        );
    }

    public static function fromPersistence(
        string $id,
        ?string $productId,
        string $description,
        int $quantity,
        int $unitPriceCents,
        int $lineTotalCents
    ): self {
        return new self($id, $productId, $description, $quantity, $unitPriceCents, $lineTotalCents);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function productId(): ?string
    {
        return $this->productId;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function lineTotalCents(): int
    {
        return $this->lineTotalCents;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->productId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unitPrice' => Money::toDecimal($this->unitPriceCents),
            'lineTotal' => Money::toDecimal($this->lineTotalCents),
        ];
    }
}
