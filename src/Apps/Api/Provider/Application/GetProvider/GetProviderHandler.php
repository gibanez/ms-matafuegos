<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\GetProvider;

use Gaby\Apps\Api\Provider\Domain\Exception\ProviderNotFoundException;
use Gaby\Apps\Api\Provider\Domain\Provider;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;

final class GetProviderHandler
{
    /** @var ProviderRepositoryInterface */
    private $providers;

    public function __construct(ProviderRepositoryInterface $providers)
    {
        $this->providers = $providers;
    }

    public function handle(GetProviderQuery $query): Provider
    {
        $provider = $this->providers->findById($query->id);

        if ($provider === null) {
            throw ProviderNotFoundException::withId($query->id);
        }

        return $provider;
    }
}
