<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Infrastructure\Persistence;

use Gaby\Apps\Api\Customer\Domain\Customer;
use Gaby\Apps\Api\Customer\Domain\CustomerRepositoryInterface;
use PDO;

final class SqliteCustomerRepository implements CustomerRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Customer
    {
        $statement = $this->pdo->prepare('SELECT * FROM customers WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @return Customer[]
     */
    public function findAll(?string $q = null): array
    {
        $sql = 'SELECT * FROM customers';
        $params = [];

        if ($q !== null && trim($q) !== '') {
            $sql .= ' WHERE name LIKE :q OR tax_id LIKE :q OR email LIKE :q OR phone LIKE :q';
            $params['q'] = '%' . trim($q) . '%';
        }

        $sql .= ' ORDER BY name ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function existsByTaxId(?string $taxId, ?string $excludeId = null): bool
    {
        if ($taxId === null || $taxId === '') {
            return false;
        }

        $sql = 'SELECT COUNT(*) FROM customers WHERE tax_id = :tax_id';
        $params = ['tax_id' => $taxId];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function save(Customer $customer): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO customers
                (id, name, tax_id, email, phone, address, city, notes, status, created_at, updated_at)
             VALUES
                (:id, :name, :tax_id, :email, :phone, :address, :city, :notes, :status, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                tax_id = excluded.tax_id,
                email = excluded.email,
                phone = excluded.phone,
                address = excluded.address,
                city = excluded.city,
                notes = excluded.notes,
                status = excluded.status,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'id' => $customer->id(),
            'name' => $customer->name(),
            'tax_id' => $customer->taxId(),
            'email' => $customer->email(),
            'phone' => $customer->phone(),
            'address' => $customer->address(),
            'city' => $customer->city(),
            'notes' => $customer->notes(),
            'status' => $customer->status(),
            'created_at' => $customer->createdAt(),
            'updated_at' => $customer->updatedAt(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM customers WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function isReferenced(string $id): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT
                (SELECT COUNT(*) FROM quotes WHERE customer_id = :id)
                + (SELECT COUNT(*) FROM invoices WHERE customer_id = :id)'
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Customer
    {
        return Customer::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            $this->nullableString($row['tax_id']),
            $this->nullableString($row['email']),
            $this->nullableString($row['phone']),
            $this->nullableString($row['address']),
            $this->nullableString($row['city']),
            $this->nullableString($row['notes']),
            (string) $row['status'],
            (string) $row['created_at'],
            (string) $row['updated_at']
        );
    }

    /**
     * @param mixed $value
     */
    private function nullableString($value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
