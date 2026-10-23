<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\GetProvider;

final class GetProviderQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
