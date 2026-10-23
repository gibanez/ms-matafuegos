<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\CreateCustomer;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use Gaby\Apps\Api\Customer\Domain\Exception\CustomerTaxIdAlreadyInUseException;

final class CreateCustomerHandler
{
    /** @var CustomerRepositoryInterface */
    private $customers;

    public function __construct(CustomerRepositoryInterface $customers)
    {
        $this->customers = $customers;
    }

    public function handle(CreateCustomerCommand $command): Customer
    {
        if ($command->taxId !== null && $this->customers->existsByTaxId($command->taxId)) {
            throw CustomerTaxIdAlreadyInUseException::withTaxId($command->taxId);
        }

        $customer = Customer::create(
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
