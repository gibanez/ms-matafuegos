<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\DeleteOwnerMedia;

use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\MediaStorageInterface;

final class DeleteOwnerMediaHandler
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

    public function handle(DeleteOwnerMediaCommand $command): void
    {
        $items = $this->media->findByOwner($command->ownerType, $command->ownerId);

        foreach ($items as $item) {
            $this->storage->delete($item->path());
            $this->media->delete($item->id());
        }
    }
}
