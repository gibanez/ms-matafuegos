<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\GetInvoice;

use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotFoundException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class GetInvoiceHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    public function __construct(InvoiceRepositoryInterface $invoices)
    {
        $this->invoices = $invoices;
    }

    public function handle(GetInvoiceQuery $query): Invoice
    {
        $invoice = $this->invoices->findById($query->id);

        if ($invoice === null) {
            throw InvoiceNotFoundException::withId($query->id);
        }

        return $invoice;
    }
}
