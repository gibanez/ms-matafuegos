<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\CreateInvoiceFromQuote;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Invoice\Domain\Exception\InvalidInvoiceException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceAdjustment;
use Gaby\Apps\Api\Invoice\Domain\InvoiceLine;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class CreateInvoiceFromQuoteHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    /** @var QuoteRepositoryInterface */
    private $quotes;

    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(
        InvoiceRepositoryInterface $invoices,
        QuoteRepositoryInterface $quotes,
        CustomerRepositoryInterface $customers
    ) {
        $this->invoices = $invoices;
        $this->quotes = $quotes;
        $this->customers = $customers;
    }

    public function handle(CreateInvoiceFromQuoteCommand $command): Invoice
    {
        $quote = $this->quotes->findById($command->quoteId);

        if ($quote === null) {
            throw InvalidInvoiceException::quoteNotFound($command->quoteId);
        }

        if ($quote->status() === Quote::STATUS_CANCELLED) {
            throw InvalidInvoiceException::quoteCancelled($command->quoteId);
        }

        if ($this->invoices->existsByQuoteId($command->quoteId)) {
            throw InvalidInvoiceException::quoteAlreadyInvoiced($command->quoteId);
        }

        if ($this->customers->findById($quote->customerId()) === null) {
            throw InvalidInvoiceException::customerNotFound($quote->customerId());
        }

        $lines = [];

        foreach ($quote->lines() as $line) {
            $lines[] = InvoiceLine::create(
                $line->productId(),
                $line->description(),
                $line->quantity(),
                $line->unitPriceCents()
            );
        }

        $adjustments = [];

        foreach ($quote->adjustments() as $adjustment) {
            $adjustments[] = InvoiceAdjustment::create(
                $adjustment->description(),
                $adjustment->kind(),
                $adjustment->valueType(),
                $adjustment->value()
            );
        }

        $sequence = $this->invoices->nextSequence();
        $number = sprintf('FAC-%06d', $sequence);

        $invoice = Invoice::create(
            $number,
            $sequence,
            $quote->customerId(),
            $quote->id(),
            date('Y-m-d'),
            null,
            null,
            $quote->notes(),
            $lines,
            $adjustments,
            date('c')
        );

        $this->invoices->save($invoice);

        $quote->changeStatus(Quote::STATUS_ACCEPTED, date('c'));
        $this->quotes->save($quote);

        return $invoice;
    }
}
