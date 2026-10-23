<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\MarkNotified;

final class MarkNotifiedCommand
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
