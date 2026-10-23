<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\CreateQuote;

use Gaby\Apps\Api\Quote\Domain\Quote;

final class CreateQuoteCommand
{
    /** @var string */
    public $customerId;

    /** @var string */
    public $issueDate;

    /** @var string|null */
    public $validUntil;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $status;

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
        ?string $validUntil,
        ?string $notes,
        string $status,
        array $lines,
        array $adjustments
    ) {
        $this->customerId = $customerId;
        $this->issueDate = $issueDate;
        $this->validUntil = $validUntil;
        $this->notes = $notes;
        $this->status = $status;
        $this->lines = $lines;
        $this->adjustments = $adjustments;
    }
}
