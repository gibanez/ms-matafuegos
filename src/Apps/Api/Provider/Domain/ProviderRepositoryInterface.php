<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Domain;

interface ProviderRepositoryInterface
{
    public function findById(string $id): ?Provider;

    /**
     * @return Provider[]
     */
    public function findAll(bool $activeOnly = false): array;

    public function save(Provider $provider): void;

    public function delete(string $id): void;
}
