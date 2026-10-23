<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Domain;

use Ramsey\Uuid\Uuid;

final class Category
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $slug;

    /** @var string|null */
    private $description;

    /** @var string */
    private $status;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    private function __construct(
        string $id,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $name,
        string $slug,
        ?string $description,
        string $status,
        string $now
    ): self {
        $id = Uuid::uuid4()->toString();

        return new self($id, $name, $slug, $description, $status, $now, $now);
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $slug,
        ?string $description,
        string $status,
        string $createdAt,
        string $updatedAt
    ): self {
        return new self($id, $name, $slug, $description, $status, $createdAt, $updatedAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
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
        string $slug,
        ?string $description,
        string $status,
        string $now
    ): void {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
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
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
