<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Application\CreateMedia;

final class CreateMediaCommand
{
    /** @var string */
    public $ownerType;

    /** @var string */
    public $ownerId;

    /** @var string */
    public $type;

    /** @var string */
    public $path;

    /** @var string */
    public $originalFilename;

    /** @var string */
    public $mimeType;

    /** @var int|null */
    public $position;

    public function __construct(
        string $ownerType,
        string $ownerId,
        string $type,
        string $path,
        string $originalFilename,
        string $mimeType,
        ?int $position = null
    ) {
        $this->ownerType = $ownerType;
        $this->ownerId = $ownerId;
        $this->type = $type;
        $this->path = $path;
        $this->originalFilename = $originalFilename;
        $this->mimeType = $mimeType;
        $this->position = $position;
    }
}
