<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\CreateInvoice;

final class CreateInvoiceCommand
{
    /** @var string */
    public $customerId;

    /** @var string */
    public $issueDate;

    /** @var string|null */
    public $dueDate;

    /** @var string|null */
    public $paymentMethod;

    /** @var string|null */
    public $notes;

    /** @var array<int, array<string, mixed>> */
    public $lines;

    /** @var array<int, array<string, mixed>> */
    public $adjustments;

    /**
     * @param array<int, array<string, mixed>> $lines
     * @param array<int, array<string, mixed>> $adjustments
     */
    public function __construct(
        string $customerId,
        string $issueDate,
        ?string $dueDate,
        ?string $paymentMethod,
        ?string $notes,
        array $lines,
        array $adjustments
    ) {
        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->dueDate = $dueDate;
        $this->paymentMethod = $paymentMethod;
        $this->notes = $notes;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
    }
}
