<?php

declare(strict_types=1);

use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;
use Gaby\Apps\Api\Invoice\Infrastructure\Persistence\SqliteInvoiceRepository;
use function DI\autowire;

return [
    InvoiceRepositoryInterface::class => autowire(SqliteInvoiceRepository::class),
];
