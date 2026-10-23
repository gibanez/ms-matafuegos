<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\UpdateInvoice;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Invoice\Application\Support\InvoiceInputResolver;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotFoundException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class UpdateInvoiceHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    /** @var CustomerRepositoryInterface */
    private $customers;

    /** @var InvoiceInputResolver */
    private $resolver;

    public function __construct(
        InvoiceRepositoryInterface $invoices,
        CustomerRepositoryInterface $customers,
        InvoiceInputResolver $resolver
    ) {
        $this->invoices = $invoices;
        $this->customers = $customers;
        $this->resolver = $resolver;
    }

    public function handle(UpdateInvoiceCommand $command): Invoice
    {
        $invoice = $this->invoices->findById($command->id);

        if ($invoice === null) {
            throw InvoiceNotFoundException::withId($command->id);
        }

        if ($this->customers->findById($command->customerId) === null) {
            throw InvalidInvoiceException::customerNotFound($command->customerId);
        }

        $lines = $this->resolver->resolveLines($command->lines);
        $adjustments = $this->resolver->resolveAdjustments($command->adjustments);

        $invoice->updateDetails(
            $command->customerId,
            $command->issueDate,
            $command->dueDate,
            $command->paymentMethod,
            $command->notes,
            $lines,
            $adjustments,
            date('c')
        );

        $this->invoices->save($invoice);

        return $invoice;
    }
}
