<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Domain\Exception;

use RuntimeException;

final class InvalidInvoiceException extends RuntimeException
{
    public static function customerNotFound(string $id): self
    {
        return new self('Customer not found: ' . $id);
    }

    public static function productNotFound(string $id): self
    {
        return new self('Product not found: ' . $id);
    }

    public static function quoteNotFound(string $id): self
    {
        return new self('Quote not found: ' . $id);
    }

    public static function quoteCancelled(string $id): self
    {
        return new self('Quote is cancelled and cannot be invoiced: ' . $id);
    }

    public static function quoteAlreadyInvoiced(string $id): self
    {
        return new self('Quote already has an invoice: ' . $id);
    }

    public static function emptyLines(): self
    {
        return new self('An invoice must have at least one line.');
    }

    public static function invalidLine(): self
    {
        return new self('Each line needs a description.');
    }

    public static function invalidQuantity(): self
    {
        return new self('Quantity must be a positive integer.');
    }

    public static function invalidPrice(): self
    {
        return new self('Unit price cannot be negative.');
    }

    public static function invalidAdjustment(): self
    {
        return new self('Invalid adjustment.');
    }

    public static function negativeTotal(): self
    {
        return new self('Adjustments cannot make the total negative.');
    }

    public static function invalidDate(): self
    {
        return new self('Invalid date. Expected format: Y-m-d.');
    }

    public static function invalidDateRange(): self
    {
        return new self('Due date must be greater than or equal to the issue date.');
    }

    public static function invalidStatus(string $status): self
    {
        return new self('Invalid invoice status: ' . $status);
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self('Invalid invoice status transition: ' . $from . ' -> ' . $to);
    }
}
