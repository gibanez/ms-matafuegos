<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\GetQuote;

use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotFoundException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class GetQuoteHandler
{
    /** @var QuoteRepositoryInterface */
    private $quotes;

    public function __construct(QuoteRepositoryInterface $quotes)
    {
        $this->quotes = $quotes;
    }

    public function handle(GetQuoteQuery $query): Quote
    {
        $quote = $this->quotes->findById($query->id);

        if ($quote === null) {
            throw QuoteNotFoundException::withId($query->id);
        }

        return $quote;
    }
}
