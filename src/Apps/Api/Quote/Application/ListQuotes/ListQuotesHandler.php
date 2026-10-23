<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\ListQuotes;

use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class ListQuotesHandler
{
    /** @var QuoteRepositoryInterface */
    private $quotes;

    public function __construct(QuoteRepositoryInterface $quotes)
    {
        $this->quotes = $quotes;
    }

    /**
     * @return \Gaby\Apps\Api\Quote\Domain\Quote[]
     */
    public function handle(ListQuotesQuery $query): array
    {
        return $this->quotes->findAll($query->filters);
    }
}
