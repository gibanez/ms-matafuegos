<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\DeleteInvoice;

use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotEditableException;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotFoundException;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class DeleteInvoiceHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    public function __construct(InvoiceRepositoryInterface $invoices)
    {
        $this->invoices = $invoices;
    }

    public function handle(DeleteInvoiceCommand $command): void
    {
        $invoice = $this->invoices->findById($command->id);

        if ($invoice === null) {
            throw InvoiceNotFoundException::withId($command->id);
        }

        if (!$invoice->isDraft()) {
            throw InvoiceNotEditableException::notDraft($command->id);
        }

        $this->invoices->delete($command->id);
    }
}
