<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\GetCustomer;

final class GetCustomerQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
