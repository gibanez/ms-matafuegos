<?php

declare(strict_types=1);

use Gaby\Apps\Api\Quote\Domain\QuoteRepositoryInterface;
use Gaby\Apps\Api\Quote\Infrastructure\Persistence\SqliteQuoteRepository;
use function DI\autowire;

return [
    QuoteRepositoryInterface::class => autowire(SqliteQuoteRepository::class),
];
