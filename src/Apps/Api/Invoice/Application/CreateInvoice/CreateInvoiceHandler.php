<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\CreateInvoice;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Invoice\Application\Support\InvoiceInputResolver;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;

final class CreateInvoiceHandler
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

    public function handle(CreateInvoiceCommand $command): Invoice
    {
        if ($this->customers->findById($command->customerId) === null) {
            throw InvalidInvoiceException::customerNotFound($command->customerId);
        }

        $lines = $this->resolver->resolveLines($command->lines);
        $adjustments = $this->resolver->resolveAdjustments($command->adjustments);
        $sequence = $this->invoices->nextSequence();
        $number = sprintf('FAC-%06d', $sequence);

        $invoice = Invoice::create(
            $number,
            $sequence,
            $command->customerId,
            null,
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
