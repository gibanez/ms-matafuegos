<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\ListQuotes;

final class ListQuotesQuery
{
    /** @var array<string, mixed> */
    public $filters;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }
}
