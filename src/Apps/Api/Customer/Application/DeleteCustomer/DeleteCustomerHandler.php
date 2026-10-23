<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\DeleteCustomer;

use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerInUseException;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerNotFoundException;

final class DeleteCustomerHandler
{
    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(CustomerRepositoryInterface $customers)
    {
        $this->customers = $customers;
    }

    public function handle(DeleteCustomerCommand $command): void
    {
        $customer = $this->customers->findById($command->id);

        if ($customer === null) {
            throw CustomerNotFoundException::withId($command->id);
        }

        if ($this->customers->isReferenced($command->id)) {
            throw CustomerInUseException::withId($command->id);
        }

        $this->customers->delete($command->id);
    }
}
