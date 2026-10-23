<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus;

final class ChangeInvoiceStatusCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $status;

    public function __construct(string $id, string $status)
    {
        $this->id = $id;
        $this->status = $status;
    }
}
