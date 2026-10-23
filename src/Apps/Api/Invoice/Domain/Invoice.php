<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Domain;

use Gaby\Shared\Domain\Money;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Invoice
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    /** @var string */
    private $id;

    /** @var string */
    private $number;

    /** @var int */
    private $sequence;

    /** @var string */
    private $customerId;

    /** @var string|null */
    private $quoteId;

    /** @var string */
    private $issueDate;

    /** @var string|null */
    private $dueDate;

    /** @var string|null */
    private $paidAt;

    /** @var string */
    private $status;

    /** @var string|null */
    private $paymentMethod;

    /** @var string|null */
    private $notes;

    /** @var InvoiceLine[] */
    private $lines;

    /** @var InvoiceAdjustment[] */
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
     * @param InvoiceLine[] $lines
     * @param InvoiceAdjustment[] $adjustments
     */
    private function __construct(
        string $id,
        string $number,
        int $sequence,
        string $customerId,
        ?string $quoteId,
        string $issueDate,
        ?string $dueDate,
        ?string $paidAt,
        string $status,
        ?string $paymentMethod,
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
        $this->quoteId = $quoteId;
        $this->issueDate = $issueDate;
        $this->dueDate = $dueDate;
        $this->paidAt = $paidAt;
        $this->status = $status;
        $this->paymentMethod = $paymentMethod;
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
     * @param InvoiceLine[] $lines
     * @param InvoiceAdjustment[] $adjustments
     */
    public static function create(
        string $number,
        int $sequence,
        string $customerId,
        ?string $quoteId,
        string $issueDate,
        ?string $dueDate,
        ?string $paymentMethod,
        ?string $notes,
        array $lines,
        array $adjustments,
        string $now
    ): self {
        self::assertValidDates($issueDate, $dueDate);

        if ($lines === []) {
            throw Exception\InvalidInvoiceException::emptyLines();
        }

        $invoice = new self(
            Uuid::uuid4()->toString(),
            $number,
            $sequence,
            $customerId,
            $quoteId,
            $issueDate,
            $dueDate,
            null,
            self::STATUS_DRAFT,
            $paymentMethod,
            $notes,
            $lines,
            $adjustments,
            0,
            0,
            0,
            $now,
            $now
        );

        $invoice->recalculate();

        return $invoice;
    }

    /**
     * @param InvoiceLine[] $lines
     * @param InvoiceAdjustment[] $adjustments
     */
    public static function fromPersistence(
        string $id,
        string $number,
        int $sequence,
        string $customerId,
        ?string $quoteId,
        string $issueDate,
        ?string $dueDate,
        ?string $paidAt,
        string $status,
        ?string $paymentMethod,
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
            $quoteId,
            $issueDate,
            $dueDate,
            $paidAt,
            $status,
            $paymentMethod,
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

    public function quoteId(): ?string
    {
        return $this->quoteId;
    }

    public function issueDate(): string
    {
        return $this->issueDate;
    }

    public function dueDate(): ?string
    {
        return $this->dueDate;
    }

    public function paidAt(): ?string
    {
        return $this->paidAt;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return InvoiceLine[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return InvoiceAdjustment[]
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

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function hasStockApplied(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_PAID], true);
    }

    /**
     * @param InvoiceLine[] $lines
     * @param InvoiceAdjustment[] $adjustments
     */
    public function updateDetails(
        string $customerId,
        string $issueDate,
        ?string $dueDate,
        ?string $paymentMethod,
        ?string $notes,
        array $lines,
        array $adjustments,
        string $now
    ): void {
        if (!$this->isDraft()) {
            throw Exception\InvoiceNotEditableException::notDraft($this->id);
        }

        self::assertValidDates($issueDate, $dueDate);

        if ($lines === []) {
            throw Exception\InvalidInvoiceException::emptyLines();
        }

        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->dueDate = $dueDate;
        $this->paymentMethod = $paymentMethod;
        $this->notes = $notes;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
        $this->recalculate();
        $this->updatedAt = $now;
    }

    public function changeStatus(string $status, string $now): void
    {
        self::assertValidStatus($status);

        if ($status === $this->status) {
            return;
        }

        if ($this->status === self::STATUS_CANCELLED) {
            throw Exception\InvoiceNotEditableException::cancelled($this->id);
        }

        $allowed = [
            self::STATUS_DRAFT => [self::STATUS_ISSUED, self::STATUS_CANCELLED],
            self::STATUS_ISSUED => [self::STATUS_PAID, self::STATUS_CANCELLED],
            self::STATUS_PAID => [self::STATUS_CANCELLED],
            self::STATUS_CANCELLED => [],
        ];

        if (!in_array($status, $allowed[$this->status], true)) {
            throw Exception\InvalidInvoiceException::invalidTransition($this->status, $status);
        }

        $this->status = $status;

        if ($status === self::STATUS_PAID) {
            $this->paidAt = substr($now, 0, 10);
        }

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
            'quoteId' => $this->quoteId,
            'issueDate' => $this->issueDate,
            'dueDate' => $this->dueDate,
            'paidAt' => $this->paidAt,
            'status' => $this->status,
            'paymentMethod' => $this->paymentMethod,
            'notes' => $this->notes,
            'subtotal' => Money::toDecimal($this->subtotalCents),
            'adjustmentsTotal' => Money::toDecimal($this->adjustmentsTotalCents),
            'total' => Money::toDecimal($this->totalCents),
            'lines' => array_map(function (InvoiceLine $line): array {
                return $line->toArray();
            }, $this->lines),
            'adjustments' => array_map(function (InvoiceAdjustment $adjustment): array {
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
            throw Exception\InvalidInvoiceException::negativeTotal();
        }

        $this->subtotalCents = $subtotal;
        $this->adjustmentsTotalCents = $adjustmentsTotal;
        $this->totalCents = $total;
    }

    private static function assertValidStatus(string $status): void
    {
        if (!in_array($status, [
            self::STATUS_DRAFT,
            self::STATUS_ISSUED,
            self::STATUS_PAID,
            self::STATUS_CANCELLED,
        ], true)) {
            throw Exception\InvalidInvoiceException::invalidStatus($status);
        }
    }

    private static function assertValidDates(string $issueDate, ?string $dueDate): void
    {
        if (!self::isValidDate($issueDate)) {
            throw Exception\InvalidInvoiceException::invalidDate();
        }

        if ($dueDate === null) {
            return;
        }

        if (!self::isValidDate($dueDate) || $dueDate < $issueDate) {
            throw Exception\InvalidInvoiceException::invalidDateRange();
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
