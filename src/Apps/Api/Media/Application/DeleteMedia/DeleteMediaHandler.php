<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\DeleteMedia;

use Gaby\Apps\Api\Media\Domain\Exception\MediaNotFoundException;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\MediaStorageInterface;

final class DeleteMediaHandler
{
    /** @var MediaRepositoryInterface */
    private $media;

    /** @var MediaStorageInterface */
    private $storage;

    public function __construct(MediaRepositoryInterface $media, MediaStorageInterface $storage)
    {
        $this->media = $media;
        $this->storage = $storage;
    }

    public function handle(DeleteMediaCommand $command): void
    {
        $media = $this->media->findById($command->id);

        if (
            $media === null
            || $media->ownerType() !== $command->ownerType
            || $media->ownerId() !== $command->ownerId
        ) {
            throw MediaNotFoundException::withId($command->id);
        }

        $this->storage->delete($media->path());
        $this->media->delete($media->id());
    }
}
