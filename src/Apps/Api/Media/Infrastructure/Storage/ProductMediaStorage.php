<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Infrastructure\Storage;

use Gaby\Apps\Api\Media\Domain\Exception\InvalidMediaException;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\MediaStorageInterface;
use Gaby\Shared\Config\Env;
use Psr\Http\Message\UploadedFileInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

final class ProductMediaStorage implements MediaStorageInterface
{
    private const MAX_BYTES = 10485760;

    /** @var array<string, array{type: string, extension: string}> */
    private const ALLOWED_TYPES = [
        'image/jpeg' => ['type' => Media::TYPE_IMAGE, 'extension' => 'jpg'],
        'image/png' => ['type' => Media::TYPE_IMAGE, 'extension' => 'png'],
        'image/webp' => ['type' => Media::TYPE_IMAGE, 'extension' => 'webp'],
        'image/gif' => ['type' => Media::TYPE_IMAGE, 'extension' => 'gif'],
        'video/mp4' => ['type' => Media::TYPE_VIDEO, 'extension' => 'mp4'],
        'video/webm' => ['type' => Media::TYPE_VIDEO, 'extension' => 'webm'],
        'video/quicktime' => ['type' => Media::TYPE_VIDEO, 'extension' => 'mov'],
    ];

    /**
     * @return array{type: string, path: string, originalFilename: string, mimeType: string}
     */
    public function store(UploadedFileInterface $file, string $ownerId): array
    {
        if (preg_match('/^[a-zA-Z0-9-]+$/', $ownerId) !== 1) {
            throw InvalidMediaException::invalidPath();
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw InvalidMediaException::uploadFailed();
        }

        $size = $file->getSize();

        if ($size === null || $size <= 0) {
            throw InvalidMediaException::uploadFailed();
        }

        if ($size > self::MAX_BYTES) {
            throw InvalidMediaException::tooLarge(self::MAX_BYTES);
        }

        $mimeType = $this->detectMimeType($file);

        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            throw InvalidMediaException::unsupportedType($mimeType);
        }

        $allowed = self::ALLOWED_TYPES[$mimeType];
        $filename = Uuid::uuid4()->toString() . '.' . $allowed['extension'];
        $directory = $this->publicPath() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'products'
            . DIRECTORY_SEPARATOR . $ownerId;

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw InvalidMediaException::uploadFailed();
        }

        $target = $directory . DIRECTORY_SEPARATOR . $filename;

        try {
            $file->moveTo($target);
        } catch (Throwable $error) {
            throw InvalidMediaException::uploadFailed();
        }

        $originalFilename = $file->getClientFilename();

        if ($originalFilename === null || $originalFilename === '') {
            $originalFilename = 'file.' . $allowed['extension'];
        }

        return [
            'type' => $allowed['type'],
            'path' => 'media/products/' . $ownerId . '/' . $filename,
            'originalFilename' => $originalFilename,
            'mimeType' => $mimeType,
        ];
    }

    public function delete(string $path): void
    {
        $relative = ltrim(str_replace('\\', '/', $path), '/');

        if (strpos($relative, 'media/') !== 0) {
            throw InvalidMediaException::invalidPath();
        }

        $mediaRoot = realpath($this->publicPath() . DIRECTORY_SEPARATOR . 'media');

        if ($mediaRoot === false) {
            return;
        }

        $fullPath = realpath($this->publicPath() . DIRECTORY_SEPARATOR . $relative);

        if ($fullPath === false) {
            return;
        }

        if (strpos($fullPath, $mediaRoot) !== 0) {
            throw InvalidMediaException::invalidPath();
        }

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $ownerDirectory = dirname($fullPath);

        if ($ownerDirectory !== $mediaRoot && is_dir($ownerDirectory)) {
            @rmdir($ownerDirectory);
        }
    }

    private function detectMimeType(UploadedFileInterface $file): string
    {
        $metadata = $file->getStream()->getMetadata('uri');
        $temporaryPath = is_string($metadata) ? $metadata : '';

        if ($temporaryPath !== '' && is_file($temporaryPath) && class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($temporaryPath);

            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }

        return (string) $file->getClientMediaType();
    }

    private function publicPath(): string
    {
        return Env::rootPath() . DIRECTORY_SEPARATOR . 'public';
    }
}
