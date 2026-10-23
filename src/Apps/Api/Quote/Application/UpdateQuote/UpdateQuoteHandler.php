<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\UpdateQuote;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Quote\Application\Support\QuoteInputResolver;
use Gaby\Apps\Api\Quote\Domain\Exception\InvalidQuoteException;
use Gaby\Apps\Api\Quote\Domain\Exception\QuoteNotFoundException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class UpdateQuoteHandler
{
    /** @var QuoteRepositoryInterface */
    private $quotes;

    /** @var CustomerRepositoryInterface */
    private $customers;

    /** @var QuoteInputResolver */
    private $resolver;

    public function __construct(
        QuoteRepositoryInterface $quotes,
        CustomerRepositoryInterface $customers,
        QuoteInputResolver $resolver
    ) {
        $this->quotes = $quotes;
        $this->customers = $customers;
        $this->resolver = $resolver;
    }

    public function handle(UpdateQuoteCommand $command): Quote
    {
        $quote = $this->quotes->findById($command->id);

        if ($quote === null) {
            throw QuoteNotFoundException::withId($command->id);
        }

        if ($this->customers->findById($command->customerId) === null) {
            throw InvalidQuoteException::customerNotFound($command->customerId);
        }

        $lines = $this->resolver->resolveLines($command->lines);
        $adjustments = $this->resolver->resolveAdjustments($command->adjustments);

        $quote->updateDetails(
            $command->customerId,
            $command->issueDate,
            $command->validUntil,
            $command->notes,
            $lines,
            $adjustments,
            date('c')
        );

        $this->quotes->save($quote);

        return $quote;
    }
}
