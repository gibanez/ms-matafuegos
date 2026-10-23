<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\GetProductBySlug;

final class GetProductBySlugQuery
{
    /** @var string */
    public $slug;

    /** @var bool */
    public $activeOnly;

    public function __construct(string $slug, bool $activeOnly = false)
    {
        $this->slug = $slug;
        $this->activeOnly = $activeOnly;
    }
}
