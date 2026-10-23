<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Quote\Application\GetQuote;

final class GetQuoteQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
