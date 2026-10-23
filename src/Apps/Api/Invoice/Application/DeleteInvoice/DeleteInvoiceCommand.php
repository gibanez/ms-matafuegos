<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\DeleteInvoice;

final class DeleteInvoiceCommand
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
