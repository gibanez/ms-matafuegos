<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\UpdateQuote;

final class UpdateQuoteCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $customerId;

    /** @var string */
    public $issueDate;

    /** @var string|null */
    public $validUntil;

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
        string $id,
        string $customerId,
        string $issueDate,
        ?string $validUntil,
        ?string $notes,
        array $lines,
        array $adjustments
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->validUntil = $validUntil;
        $this->notes = $notes;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
    }
}
