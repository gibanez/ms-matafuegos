<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\CreateQuote;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Quote\Application\Support\QuoteInputResolver;
use Gaby\Apps\Api\Quote\Domain\Exception\InvalidQuoteException;
use Gaby\Apps\Api\Quote\Domain\Quote;
use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;

final class CreateQuoteHandler
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

    public function handle(CreateQuoteCommand $command): Quote
    {
        if ($this->customers->findById($command->customerId) === null) {
            throw InvalidQuoteException::customerNotFound($command->customerId);
        }

        $lines = $this->resolver->resolveLines($command->lines);
        $adjustments = $this->resolver->resolveAdjustments($command->adjustments);
        $sequence = $this->quotes->nextSequence();
        $number = sprintf('PRE-%06d', $sequence);

        $quote = Quote::create(
            $number,
            $sequence,
            $command->customerId,
            $command->issueDate,
            $command->validUntil,
            $command->notes,
            $command->status,
            $lines,
            $adjustments,
            date('c')
        );

        $this->quotes->save($quote);

        return $quote;
    }
}
