<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Domain;

interface MediaStorageInterface
{
    public function delete(string $path): void;
}
