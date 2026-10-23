<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Domain;

use Ramsey\Uuid\Uuid;

final class Provider
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string|null */
    private $taxId;

    /** @var string|null */
    private $contactName;

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $phone;

    /** @var string|null */
    private $address;

    /** @var string|null */
    private $notes;

    /** @var string */
    private $status;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    private function __construct(
        string $id,
        string $name,
        ?string $taxId,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $notes,
        string $status,
        string $createdAt,
        string $updatedAt
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
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $name,
        ?string $taxId,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $notes,
        string $status,
        string $now
    ): self {
        return new self(
            Uuid::uuid4()->toString(),
            $name,
            $taxId,
            $contactName,
            $email,
            $phone,
            $address,
            $notes,
            $status,
            $now,
            $now
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        ?string $taxId,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $notes,
        string $status,
        string $createdAt,
        string $updatedAt
    ): self {
        return new self(
            $id,
            $name,
            $taxId,
            $contactName,
            $email,
            $phone,
            $address,
            $notes,
            $status,
            $createdAt,
            $updatedAt
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function taxId(): ?string
    {
        return $this->taxId;
    }

    public function contactName(): ?string
    {
        return $this->contactName;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function updateDetails(
        string $name,
        ?string $taxId,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $notes,
        string $status,
        string $now
    ): void {
        $this->name = $name;
        $this->taxId = $taxId;
        $this->contactName = $contactName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->notes = $notes;
        $this->status = $status;
        $this->updatedAt = $now;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'taxId' => $this->taxId,
            'contactName' => $this->contactName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
