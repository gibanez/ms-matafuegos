<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\ListProviders;

use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;

final class ListProvidersHandler
{
    /** @var ProviderRepositoryInterface */
    private $providers;

    public function __construct(ProviderRepositoryInterface $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return \Gaby\Apps\Api\Provider\Domain\Provider[]
     */
    public function handle(ListProvidersQuery $query): array
    {
        return $this->providers->findAll($query->activeOnly);
    }
}
