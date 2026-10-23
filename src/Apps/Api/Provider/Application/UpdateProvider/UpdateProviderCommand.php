<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\UpdateProvider;

final class UpdateProviderCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $taxId;

    /** @var string|null */
    public $contactName;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $phone;

    /** @var string|null */
    public $address;

    /** @var string|null */
    public $notes;

    /** @var string */
    public $status;

    public function __construct(
        string $id,
        string $name,
        ?string $taxId,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $notes,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->taxId = $taxId;
        $this->contactName = $contactName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->notes = $notes;
        $this->status = $status;
    }
}
