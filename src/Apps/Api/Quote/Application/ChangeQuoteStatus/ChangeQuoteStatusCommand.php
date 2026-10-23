<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\ChangeQuoteStatus;

final class ChangeQuoteStatusCommand
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
