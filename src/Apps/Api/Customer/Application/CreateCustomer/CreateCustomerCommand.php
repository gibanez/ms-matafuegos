<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\CreateCustomer;

use Gaby\Apps\Api\Customer\Domain\Customer;

final class CreateCustomerCommand
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $taxId;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $phone;

    /** @var string|null */
    public $address;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $status;

    public function __construct(
        string $name,
        ?string $taxId,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $city,
        ?string $notes,
        string $status = Customer::STATUS_ACTIVE
    ) {
        $this->name = $name;
        $this->taxId = $taxId;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->city = $city;
        $this->notes = $notes;
        $this->status = $status;
    }
}
