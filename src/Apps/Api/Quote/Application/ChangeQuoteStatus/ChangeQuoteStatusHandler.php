<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\ChangeQuoteStatus;

use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotFoundException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class ChangeQuoteStatusHandler
{
    /** @var QuoteRepositoryInterface */
    private $quotes;

    public function __construct(QuoteRepositoryInterface $quotes)
    {
        $this->quotes = $quotes;
    }

    public function handle(ChangeQuoteStatusCommand $command): Quote
    {
        $quote = $this->quotes->findById($command->id);

        if ($quote === null) {
            throw QuoteNotFoundException::withId($command->id);
        }

        $quote->changeStatus($command->status, date('c'));
        $this->quotes->save($quote);

        return $quote;
    }
}
