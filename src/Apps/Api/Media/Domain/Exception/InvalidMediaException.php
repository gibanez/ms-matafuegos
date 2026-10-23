<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain\Exception;

use RuntimeException;

final class InvalidMediaException extends RuntimeException
{
    public static function unsupportedType(string $mimeType): self
    {
        return new self('Unsupported media type: ' . $mimeType);
    }

    public static function tooLarge(int $maxBytes): self
    {
        return new self('File is too large. Maximum size: ' . $maxBytes . ' bytes.');
    }

    public static function uploadFailed(): self
    {
        return new self('File upload failed.');
    }

    public static function invalidPath(): self
    {
        return new self('Invalid media path.');
    }
}
