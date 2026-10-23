<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\UpdateCustomer;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerNotFoundException;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerTaxIdAlreadyInUseException;

final class UpdateCustomerHandler
{
    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(CustomerRepositoryInterface $customers)
    {
        $this->customers = $customers;
    }

    public function handle(UpdateCustomerCommand $command): Customer
    {
        $customer = $this->customers->findById($command->id);

        if ($customer === null) {
            throw CustomerNotFoundException::withId($command->id);
        }

        if (
            $command->taxId !== null
            && $this->customers->existsByTaxId($command->taxId, $command->id)
        ) {
            throw CustomerTaxIdAlreadyInUseException::withTaxId($command->taxId);
        }

        $customer->updateDetails(
            $command->name,
            $command->taxId,
            $command->email,
            $command->phone,
            $command->address,
            $command->city,
            $command->notes,
            $command->status,
            date('c')
        );

        $this->customers->save($customer);

        return $customer;
    }
}
