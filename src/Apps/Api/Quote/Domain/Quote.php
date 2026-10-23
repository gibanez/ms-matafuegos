<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Domain;

use Gaby\Shared\Domain\Money;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Quote
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /** @var string */
    private $id;

    /** @var string */
    private $number;

    /** @var int */
    private $sequence;

    /** @var string */
    private $customerId;

    /** @var string */
    private $issueDate;

    /** @var string|null */
    private $validUntil;

    /** @var string */
    private $status;

    /** @var string|null */
    private $notes;

    /** @var QuoteLine[] */
    private $lines;

    /** @var QuoteAdjustment[] */
    private $adjustments;

    /** @var int */
    private $subtotalCents;

    /** @var int */
    private $adjustmentsTotalCents;

    /** @var int */
    private $totalCents;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    /**
     * @param QuoteLine[] $lines
     * @param QuoteAdjustment[] $adjustments
     */
    private function __construct(
        string $id,
        string $number,
        int $sequence,
        string $customerId,
        string $issueDate,
        ?string $validUntil,
        string $status,
        ?string $notes,
        array $lines,
        array $adjustments,
        int $subtotalCents,
        int $adjustmentsTotalCents,
        int $totalCents,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->sequence = $sequence;
        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->validUntil = $validUntil;
        $this->status = $status;
        $this->notes = $notes;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
        $this->subtotalCents = $subtotalCents;
        $this->adjustmentsTotalCents = $adjustmentsTotalCents;
        $this->totalCents = $totalCents;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param QuoteLine[] $lines
     * @param QuoteAdjustment[] $adjustments
     */
    public static function create(
        string $number,
        int $sequence,
        string $customerId,
        string $issueDate,
        ?string $validUntil,
        ?string $notes,
        string $status,
        array $lines,
        array $adjustments,
        string $now
    ): self {
        self::assertValidStatus($status);
        self::assertValidDates($issueDate, $validUntil);

        if ($lines === []) {
            throw Exception\InvalidQuoteException::emptyLines();
        }

        $quote = new self(
            Uuid::uuid4()->toString(),
            $number,
            $sequence,
            $customerId,
            $issueDate,
            $validUntil,
            $status,
            $notes,
            $lines,
            $adjustments,
            0,
            0,
            0,
            $now,
            $now
        );

        $quote->recalculate();

        return $quote;
    }

    /**
     * @param QuoteLine[] $lines
     * @param QuoteAdjustment[] $adjustments
     */
    public static function fromPersistence(
        string $id,
        string $number,
        int $sequence,
        string $customerId,
        string $issueDate,
        ?string $validUntil,
        string $status,
        ?string $notes,
        array $lines,
        array $adjustments,
        int $subtotalCents,
        int $adjustmentsTotalCents,
        int $totalCents,
        string $createdAt,
        string $updatedAt
    ): self {
        return new self(
            $id,
            $number,
            $sequence,
            $customerId,
            $issueDate,
            $validUntil,
            $status,
            $notes,
            $lines,
            $adjustments,
            $subtotalCents,
            $adjustmentsTotalCents,
            $totalCents,
            $createdAt,
            $updatedAt
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function issueDate(): string
    {
        return $this->issueDate;
    }

    public function validUntil(): ?string
    {
        return $this->validUntil;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return QuoteLine[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return QuoteAdjustment[]
     */
    public function adjustments(): array
    {
        return $this->adjustments;
    }

    public function subtotalCents(): int
    {
        return $this->subtotalCents;
    }

    public function adjustmentsTotalCents(): int
    {
        return $this->adjustmentsTotalCents;
    }

    public function totalCents(): int
    {
        return $this->totalCents;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @param QuoteLine[] $lines
     * @param QuoteAdjustment[] $adjustments
     */
    public function updateDetails(
        string $customerId,
        string $issueDate,
        ?string $validUntil,
        ?string $notes,
        array $lines,
        array $adjustments,
        string $now
    ): void {
        if ($this->status === self::STATUS_CANCELLED) {
            throw Exception\QuoteNotEditableException::cancelled($this->id);
        }

        self::assertValidDates($issueDate, $validUntil);

        if ($lines === []) {
            throw Exception\InvalidQuoteException::emptyLines();
        }

        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->validUntil = $validUntil;
        $this->notes = $notes;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
        $this->recalculate();
        $this->updatedAt = $now;
    }

    public function changeStatus(string $status, string $now): void
    {
        self::assertValidStatus($status);

        if ($this->status === self::STATUS_CANCELLED && $status !== self::STATUS_CANCELLED) {
            throw Exception\QuoteNotEditableException::cancelled($this->id);
        }

        $this->status = $status;
        $this->updatedAt = $now;
    }

    /**
     * @return array<string, int>
     */
    public function productQuantities(): array
    {
        $quantities = [];

        foreach ($this->lines as $line) {
            if ($line->productId() === null) {
                continue;
            }

            $productId = $line->productId();
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $line->quantity();
        }

        return $quantities;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'customerId' => $this->customerId,
            'issueDate' => $this->issueDate,
            'validUntil' => $this->validUntil,
            'status' => $this->status,
            'notes' => $this->notes,
            'subtotal' => Money::toDecimal($this->subtotalCents),
            'adjustmentsTotal' => Money::toDecimal($this->adjustmentsTotalCents),
            'total' => Money::toDecimal($this->totalCents),
            'lines' => array_map(function (QuoteLine $line): array {
                return $line->toArray();
            }, $this->lines),
            'adjustments' => array_map(function (QuoteAdjustment $adjustment): array {
                return $adjustment->toArray();
            }, $this->adjustments),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    private function recalculate(): void
    {
        $subtotal = 0;

        foreach ($this->lines as $line) {
            $subtotal += $line->lineTotalCents();
        }

        $adjustmentsTotal = 0;

        foreach ($this->adjustments as $adjustment) {
            $adjustmentsTotal += $adjustment->calculateFor($subtotal);
        }

        $total = $subtotal + $adjustmentsTotal;

        if ($total < 0) {
            throw Exception\InvalidQuoteException::negativeTotal();
        }

        $this->subtotalCents = $subtotal;
        $this->adjustmentsTotalCents = $adjustmentsTotal;
        $this->totalCents = $total;
    }

    private static function assertValidStatus(string $status): void
    {
        if (!in_array($status, [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ], true)) {
            throw Exception\InvalidQuoteException::invalidStatus($status);
        }
    }

    private static function assertValidDates(string $issueDate, ?string $validUntil): void
    {
        if (!self::isValidDate($issueDate)) {
            throw Exception\InvalidQuoteException::invalidDate();
        }

        if ($validUntil === null) {
            return;
        }

        if (!self::isValidDate($validUntil) || $validUntil < $issueDate) {
            throw Exception\InvalidQuoteException::invalidDateRange();
        }
    }

    private static function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if ($parsed === false) {
            return false;
        }

        if (is_array($errors) && ((int) $errors['warning_count'] > 0 || (int) $errors['error_count'] > 0)) {
            return false;
        }

        return $parsed->format('Y-m-d') === $date;
    }
}
