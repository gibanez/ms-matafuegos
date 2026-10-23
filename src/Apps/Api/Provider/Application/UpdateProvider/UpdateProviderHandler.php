<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\UpdateProvider;

use Gaby\Apps\Api\Provider\Domain\Exception\ProviderNotFoundException;
use Gaby\Apps\Api\Provider\Domain\Provider;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;

final class UpdateProviderHandler
{
    /** @var ProviderRepositoryInterface */
    private $providers;

    public function __construct(ProviderRepositoryInterface $providers)
    {
        $this->providers = $providers;
    }

    public function handle(UpdateProviderCommand $command): Provider
    {
        $provider = $this->providers->findById($command->id);

        if ($provider === null) {
            throw ProviderNotFoundException::withId($command->id);
        }

        $provider->updateDetails(
            $command->name,
            $command->taxId,
            $command->contactName,
            $command->email,
            $command->phone,
            $command->address,
            $command->notes,
            $command->status,
            date('c')
        );

        $this->providers->save($provider);

        return $provider;
    }
}
