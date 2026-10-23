<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain\Exception;

use RuntimeException;

final class MediaOwnerNotFoundException extends RuntimeException
{
    public static function withTypeAndId(string $ownerType, string $ownerId): self
    {
        return new self('Media owner not found: ' . $ownerType . '/' . $ownerId);
    }
}
