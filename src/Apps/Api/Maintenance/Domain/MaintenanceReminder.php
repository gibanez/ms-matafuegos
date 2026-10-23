<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Domain;

use DateTimeImmutable;

final class MaintenanceReminder
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_NOTIFIED = 'notified';

    /** @var string */
    private $invoiceLineId;

    /** @var string */
    private $invoiceId;

    /** @var string */
    private $invoiceNumber;

    /** @var string */
    private $customerId;

    /** @var string */
    private $customerName;

    /** @var string|null */
    private $customerPhone;

    /** @var string */
    private $productId;

    /** @var string */
    private $productName;

    /** @var string */
    private $purchaseDate;

    /** @var int */
    private $maintenanceDays;

    /** @var string|null */
    private $notifiedAt;

    /** @var string|null */
    private $snoozedUntil;

    public function __construct(
        string $invoiceLineId,
        string $invoiceId,
        string $invoiceNumber,
        string $customerId,
        string $customerName,
        ?string $customerPhone,
        string $productId,
        string $productName,
        string $purchaseDate,
        int $maintenanceDays,
        ?string $notifiedAt = null,
        ?string $snoozedUntil = null
    ) {
        $this->invoiceLineId = $invoiceLineId;
        $this->invoiceId = $invoiceId;
        $this->invoiceNumber = $invoiceNumber;
        $this->customerId = $customerId;
        $this->customerName = $customerName;
        $this->customerPhone = $customerPhone;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->purchaseDate = $purchaseDate;
        $this->maintenanceDays = max(1, $maintenanceDays);
        $this->notifiedAt = $notifiedAt;
        $this->snoozedUntil = $snoozedUntil;
    }

    public function id(): string
    {
        return $this->invoiceLineId;
    }

    public function invoiceId(): string
    {
        return $this->invoiceId;
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function customerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function purchaseDate(): string
    {
        return $this->purchaseDate;
    }

    public function maintenanceDays(): int
    {
        return $this->maintenanceDays;
    }

    public function notifiedAt(): ?string
    {
        return $this->notifiedAt;
    }

    public function snoozedUntil(): ?string
    {
        return $this->snoozedUntil;
    }

    public function dueDate(): string
    {
        $purchase = new DateTimeImmutable($this->purchaseDate);

        return $purchase->modify('+' . $this->maintenanceDays . ' days')->format('Y-m-d');
    }

    public function notifyDate(): string
    {
        $due = $this->dueDate();

        if ($this->snoozedUntil !== null && $this->snoozedUntil > $due) {
            return $this->snoozedUntil;
        }

        return $due;
    }

    public function status(string $today): string
    {
        if ($this->notifiedAt !== null) {
            return self::STATUS_NOTIFIED;
        }

        return $this->notifyDate() < $today ? self::STATUS_OVERDUE : self::STATUS_PENDING;
    }

    public function daysUntil(string $today): int
    {
        $todayDate = new DateTimeImmutable($today);
        $notifyDate = new DateTimeImmutable($this->notifyDate());
        $difference = $todayDate->diff($notifyDate);

        return (int) $difference->format('%r%a');
    }

    public function markNotified(string $today): void
    {
        $this->notifiedAt = $today;
        $this->snoozedUntil = null;
    }

    public function snooze(string $until): void
    {
        $this->snoozedUntil = $until;
        $this->notifiedAt = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $today): array
    {
        return [
            'id' => $this->invoiceLineId,
            'invoiceId' => $this->invoiceId,
            'invoiceNumber' => $this->invoiceNumber,
            'customerId' => $this->customerId,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'productId' => $this->productId,
            'productName' => $this->productName,
            'purchaseDate' => $this->purchaseDate,
            'dueDate' => $this->dueDate(),
            'notifyDate' => $this->notifyDate(),
            'snoozedUntil' => $this->snoozedUntil,
            'notifiedAt' => $this->notifiedAt,
            'maintenanceDays' => $this->maintenanceDays,
            'status' => $this->status($today),
            'daysUntil' => $this->daysUntil($today),
        ];
    }
}
