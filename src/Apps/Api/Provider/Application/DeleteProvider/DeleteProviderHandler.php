<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Application\DeleteProvider;

use Gaby\Apps\Api\Provider\Domain\Exception\ProviderNotFoundException;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;

final class DeleteProviderHandler
{
    /** @var ProviderRepositoryInterface */
    private $providers;

    public function __construct(ProviderRepositoryInterface $providers)
    {
        $this->providers = $providers;
    }

    public function handle(DeleteProviderCommand $command): void
    {
        $provider = $this->providers->findById($command->id);

        if ($provider === null) {
            throw ProviderNotFoundException::withId($command->id);
        }

        $this->providers->delete($command->id);
    }
}
