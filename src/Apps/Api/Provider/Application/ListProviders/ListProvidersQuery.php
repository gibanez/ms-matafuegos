<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\ListProviders;

final class ListProvidersQuery
{
    /** @var bool */
    public $activeOnly;

    public function __construct(bool $activeOnly = false)
    {
        $this->activeOnly = $activeOnly;
    }
}
