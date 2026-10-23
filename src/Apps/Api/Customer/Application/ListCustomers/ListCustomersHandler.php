<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\ListCustomers;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;

final class ListCustomersHandler
{
    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(CustomerRepositoryInterface $customers)
    {
        $this->customers = $customers;
    }

    /**
     * @return \Gaby\Apps\Api\Customer\Domain\Customer[]
     */
    public function handle(ListCustomersQuery $query): array
    {
        return $this->customers->findAll($query->q);
    }
}
