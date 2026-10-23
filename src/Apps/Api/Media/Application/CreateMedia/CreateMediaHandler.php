<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\CreateMedia;

use Gaby\Apps\Api\Media\Domain\Exception\MediaOwnerNotFoundException;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\OwnerExistenceCheckerInterface;

final class CreateMediaHandler
{
    /** @var MediaRepositoryInterface */
    private $media;

    /** @var OwnerExistenceCheckerInterface */
    private $owners;

    public function __construct(MediaRepositoryInterface $media, OwnerExistenceCheckerInterface $owners)
    {
        $this->media = $media;
        $this->owners = $owners;
    }

    public function handle(CreateMediaCommand $command): Media
    {
        if (!$this->owners->exists($command->ownerType, $command->ownerId)) {
            throw MediaOwnerNotFoundException::withTypeAndId($command->ownerType, $command->ownerId);
        }

        $position = $command->position;

        if ($position === null) {
            $position = $this->media->nextPosition($command->ownerType, $command->ownerId);
        }

        $media = Media::create(
            $command->ownerType,
            $command->ownerId,
            $command->type,
            $command->path,
            $command->originalFilename,
            $command->mimeType,
            max(0, $position),
            date('c')
        );

        $this->media->save($media);

        return $media;
    }
}
