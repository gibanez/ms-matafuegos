<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\DeleteMedia;

final class DeleteMediaCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $ownerType;

    /** @var string */
    public $ownerId;

    public function __construct(string $id, string $ownerType, string $ownerId)
    {
        $this->id = $id;
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
    }
}
