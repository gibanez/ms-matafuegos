<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\ListMedia;

use Gaby\Apps\Api\Media\Domain\Exception\MediaOwnerNotFoundException;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\OwnerExistenceCheckerInterface;

final class ListMediaHandler
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

    /**
     * @return \Gaby\Apps\Api\Media\Domain\Media[]
     */
    public function handle(ListMediaQuery $query): array
    {
        if (!$this->owners->exists($query->ownerType, $query->ownerId)) {
            throw MediaOwnerNotFoundException::withTypeAndId($query->ownerType, $query->ownerId);
        }

        return $this->media->findByOwner($query->ownerType, $query->ownerId);
    }
}
