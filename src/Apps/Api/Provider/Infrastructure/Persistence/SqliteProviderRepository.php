<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Provider\Infrastructure\Persistence;

use Gaby\Apps\Api\Provider\Domain\Provider;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;
use PDO;

final class SqliteProviderRepository implements ProviderRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Provider
    {
        $statement = $this->pdo->prepare('SELECT * FROM providers WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @return Provider[]
     */
    public function findAll(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM providers';

        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }

        $rows = $this->pdo->query($sql . ' ORDER BY name ASC')->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function save(Provider $provider): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO providers
                (id, name, tax_id, contact_name, email, phone, address, notes, status, created_at, updated_at)
             VALUES
                (:id, :name, :tax_id, :contact_name, :email, :phone, :address, :notes, :status, :created_at, :updated_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                tax_id = excluded.tax_id,
                contact_name = excluded.contact_name,
                email = excluded.email,
                phone = excluded.phone,
                address = excluded.address,
                notes = excluded.notes,
                status = excluded.status,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            'id' => $provider->id(),
            'name' => $provider->name(),
            'tax_id' => $provider->taxId(),
            'contact_name' => $provider->contactName(),
            'email' => $provider->email(),
            'phone' => $provider->phone(),
            'address' => $provider->address(),
            'notes' => $provider->notes(),
            'status' => $provider->status(),
            'created_at' => $provider->createdAt(),
            'updated_at' => $provider->updatedAt(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM providers WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Provider
    {
        return Provider::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            $this->nullableString($row['tax_id']),
            $this->nullableString($row['contact_name']),
            $this->nullableString($row['email']),
            $this->nullableString($row['phone']),
            $this->nullableString($row['address']),
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
