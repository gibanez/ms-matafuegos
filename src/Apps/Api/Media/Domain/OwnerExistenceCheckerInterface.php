<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain;

interface OwnerExistenceCheckerInterface
{
    public function exists(string $ownerType, string $ownerId): bool;
}
