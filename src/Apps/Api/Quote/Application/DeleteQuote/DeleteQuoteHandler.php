<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\DeleteQuote;

use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotFoundException;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class DeleteQuoteHandler
{
    /** @var QuoteRepositoryInterface */
    private $quotes;

    public function __construct(QuoteRepositoryInterface $quotes)
    {
        $this->quotes = $quotes;
    }

    public function handle(DeleteQuoteCommand $command): void
    {
        $quote = $this->quotes->findById($command->id);

        if ($quote === null) {
            throw QuoteNotFoundException::withId($command->id);
        }

        $this->quotes->delete($command->id);
    }
}
