<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\ListInvoices;

final class ListInvoicesQuery
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
