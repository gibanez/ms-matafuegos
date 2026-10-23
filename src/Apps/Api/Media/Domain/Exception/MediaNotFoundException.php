<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain\Exception;

use RuntimeException;

final class MediaNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self('Media not found: ' . $id);
    }
}
