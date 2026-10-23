<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Domain;

interface CustomerRepositoryInterface
{
    public function findById(string $id): ?Customer;

    /**
     * @return Customer[]
     */
    public function findAll(?string $q = null): array;

    public function existsByTaxId(?string $taxId, ?string $excludeId = null): bool;

    public function save(Customer $customer): void;

    public function delete(string $id): void;

    /**
     * Indica si el cliente tiene presupuestos o facturas asociadas.
     */
    public function isReferenced(string $id): bool;
}
