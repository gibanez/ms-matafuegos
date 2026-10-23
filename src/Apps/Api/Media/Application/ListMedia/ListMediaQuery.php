<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\ListMedia;

final class ListMediaQuery
{
    /** @var string */
    public $ownerType;

    /** @var string */
    public $ownerId;

    public function __construct(string $ownerType, string $ownerId)
    {
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
    }
}
