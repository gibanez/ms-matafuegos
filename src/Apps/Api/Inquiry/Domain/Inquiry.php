<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Domain;

use Ramsey\Uuid\Uuid;

final class Inquiry
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var string|null */
    private $phone;

    /** @var string */
    private $message;

    /** @var string */
    private $createdAt;

    private function __construct(
        string $id,
        string $name,
        string $email,
        ?string $phone,
        string $message,
        string $createdAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->message = $message;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $name,
        string $email,
        string $phone,
        string $message,
        string $createdAt
    ): self {
        return new self(Uuid::uuid4()->toString(), $name, $email, $phone, $message, $createdAt);
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $email,
        ?string $phone,
        string $message,
        string $createdAt
    ): self {
        return new self($id, $name, $email, $phone, $message, $createdAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'createdAt' => $this->createdAt,
        ];
    }
}
