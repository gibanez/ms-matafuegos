<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\DeleteCustomer;

final class DeleteCustomerCommand
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
