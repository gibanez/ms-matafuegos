<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\UpdateMediaPosition;

final class UpdateMediaPositionCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $ownerType;

    /** @var string */
    public $ownerId;

    /** @var int */
    public $position;

    public function __construct(string $id, string $ownerType, string $ownerId, int $position)
    {
        $this->id = $id;
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->position = $position;
    }
}
