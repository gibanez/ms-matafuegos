<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\GetCustomer;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerNotFoundException;

final class GetCustomerHandler
{
    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(CustomerRepositoryInterface $customers)
    {
        $this->customers = $customers;
    }

    public function handle(GetCustomerQuery $query): Customer
    {
        $customer = $this->customers->findById($query->id);

        if ($customer === null) {
            throw CustomerNotFoundException::withId($query->id);
        }

        return $customer;
    }
}
