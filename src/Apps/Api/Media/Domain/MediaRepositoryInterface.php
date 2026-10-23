<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain;

interface MediaRepositoryInterface
{
    public function findById(string $id): ?Media;

    /**
     * @return Media[]
     */
    public function findByOwner(string $ownerType, string $ownerId): array;

    public function nextPosition(string $ownerType, string $ownerId): int;

    public function save(Media $media): void;

    public function delete(string $id): void;
}
