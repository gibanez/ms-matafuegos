<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain;

use Gaby\Shared\Domain\Money;
use Ramsey\Uuid\Uuid;

final class QuoteAdjustment
{
    public const KIND_SURCHARGE = 'surcharge';
    public const KIND_DISCOUNT = 'discount';

    public const VALUE_FIXED = 'fixed';
    public const VALUE_PERCENTAGE = 'percentage';

    /** @var string */
    private $id;

    /** @var string */
    private $description;

    /** @var string */
    private $kind;

    /** @var string */
    private $valueType;

    /** @var int */
    private $value;

    /** @var int */
    private $amountCents;

    private function __construct(
        string $id,
        string $description,
        string $kind,
        string $valueType,
        int $value,
        int $amountCents
    ) {
        $this->id = $id;
        $this->description = $description;
        $this->kind = $kind;
        $this->valueType = $valueType;
        $this->value = $value;
        $this->amountCents = $amountCents;
    }

    public static function create(string $description, string $kind, string $valueType, int $value): self
    {
        if (trim($description) === '') {
            throw Exception\InvalidQuoteException::invalidAdjustment();
        }

        if (!in_array($kind, [self::KIND_SURCHARGE, self::KIND_DISCOUNT], true)) {
            throw Exception\InvalidQuoteException::invalidAdjustment();
        }

        if (!in_array($valueType, [self::VALUE_FIXED, self::VALUE_PERCENTAGE], true)) {
            throw Exception\InvalidQuoteException::invalidAdjustment();
        }

        if ($value < 0) {
            throw Exception\InvalidQuoteException::invalidAdjustment();
        }

        if ($valueType === self::VALUE_PERCENTAGE && $value > 10000) {
            throw Exception\InvalidQuoteException::invalidAdjustment();
        }

        return new self(
            Uuid::uuid4()->toString(),
            trim($description),
            $kind,
            $valueType,
            $value,
            self::signedAmount($kind, $valueType, $value, 0)
        );
    }

    public static function fromPersistence(
        string $id,
        string $description,
        string $kind,
        string $valueType,
        int $value,
        int $amountCents
    ): self {
        return new self($id, $description, $kind, $valueType, $value, $amountCents);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function valueType(): string
    {
        return $this->valueType;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function amountCents(): int
    {
        return $this->amountCents;
    }

    /**
     * Calcula el importe del ajuste sobre el subtotal y guarda el resultado.
     */
    public function calculateFor(int $baseCents): int
    {
        $this->amountCents = self::signedAmount($this->kind, $this->valueType, $this->value, $baseCents);

        return $this->amountCents;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'kind' => $this->kind,
            'valueType' => $this->valueType,
            'value' => $this->valueType === self::VALUE_FIXED
                ? Money::toDecimal($this->value)
                : round($this->value / 100, 2),
            'amount' => Money::toDecimal($this->amountCents),
        ];
    }

    private static function signedAmount(string $kind, string $valueType, int $value, int $baseCents): int
    {
        $amount = $valueType === self::VALUE_FIXED
            ? $value
            : (int) round($baseCents * $value / 10000);

        return $kind === self::KIND_DISCOUNT ? -$amount : $amount;
    }
}
