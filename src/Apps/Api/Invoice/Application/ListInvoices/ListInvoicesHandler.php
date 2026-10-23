<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\ListInvoices;

use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class ListInvoicesHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    public function __construct(InvoiceRepositoryInterface $invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @return \Gaby\Apps\Api\Invoice\Domain\Invoice[]
     */
    public function handle(ListInvoicesQuery $query): array
    {
        return $this->invoices->findAll($query->filters);
    }
}
