<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain;

use Ramsey\Uuid\Uuid;

final class Media
{
    public const OWNER_PRODUCT = 'product';

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    /** @var string */
    private $id;

    /** @var string */
    private $ownerType;

    /** @var string */
    private $ownerId;

    /** @var string */
    private $type;

    /** @var string */
    private $path;

    /** @var string */
    private $originalFilename;

    /** @var string */
    private $mimeType;

    /** @var int */
    private $position;

    /** @var string */
    private $createdAt;

    private function __construct(
        string $id,
        string $ownerType,
        string $ownerId,
        string $type,
        string $path,
        string $originalFilename,
        string $mimeType,
        int $position,
        string $createdAt
    ) {
        $this->id = $id;
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->type = $type;
        $this->path = $path;
        $this->originalFilename = $originalFilename;
        $this->mimeType = $mimeType;
        $this->position = $position;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $ownerType,
        string $ownerId,
        string $type,
        string $path,
        string $originalFilename,
        string $mimeType,
        int $position,
        string $now
    ): self {
        return new self(
            Uuid::uuid4()->toString(),
            $ownerType,
            $ownerId,
            $type,
            $path,
            $originalFilename,
            $mimeType,
            $position,
            $now
        );
    }

    public static function fromPersistence(
        string $id,
        string $ownerType,
        string $ownerId,
        string $type,
        string $path,
        string $originalFilename,
        string $mimeType,
        int $position,
        string $createdAt
    ): self {
        return new self(
            $id,
            $ownerType,
            $ownerId,
            $type,
            $path,
            $originalFilename,
            $mimeType,
            $position,
            $createdAt
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function ownerType(): string
    {
        return $this->ownerType;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatePosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ownerType' => $this->ownerType,
            'ownerId' => $this->ownerId,
            'type' => $this->type,
            'path' => $this->path,
            'url' => '/' . ltrim($this->path, '/'),
            'originalFilename' => $this->originalFilename,
            'mimeType' => $this->mimeType,
            'position' => $this->position,
            'createdAt' => $this->createdAt,
        ];
    }
}
