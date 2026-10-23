<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\UpdateCustomer;

final class UpdateCustomerCommand
{
    /** @var string */
    public $id;

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
        string $id,
        string $name,
        ?string $taxId,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $city,
        ?string $notes,
        string $status
    ) {
        $this->id = $id;
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
