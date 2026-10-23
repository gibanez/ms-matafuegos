<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\CreateInvoiceFromQuote;

final class CreateInvoiceFromQuoteCommand
{
    /** @var string */
    public $quoteId;

    public function __construct(string $quoteId)
    {
        $this->quoteId = $quoteId;
    }
}
