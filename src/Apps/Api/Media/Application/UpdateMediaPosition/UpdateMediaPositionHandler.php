<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\UpdateMediaPosition;

use Gaby\Apps\Api\Media\Domain\Exception\MediaNotFoundException;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;

final class UpdateMediaPositionHandler
{
    /** @var MediaRepositoryInterface */
    private $media;

    public function __construct(MediaRepositoryInterface $media)
    {
        $this->media = $media;
    }

    public function handle(UpdateMediaPositionCommand $command): Media
    {
        $media = $this->media->findById($command->id);

        if (
            $media === null
            || $media->ownerType() !== $command->ownerType
            || $media->ownerId() !== $command->ownerId
        ) {
            throw MediaNotFoundException::withId($command->id);
        }

        $media->updatePosition(max(0, $command->position));
        $this->media->save($media);

        return $media;
    }
}
