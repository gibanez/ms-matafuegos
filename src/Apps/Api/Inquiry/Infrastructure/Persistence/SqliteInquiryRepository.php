<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Infrastructure\Persistence;

use Gaby\Apps\Api\Inquiry\Domain\Inquiry;
use Gaby\Apps\Api\Inquiry\Domain\InquiryRepositoryInterface;
use PDO;

final class SqliteInquiryRepository implements InquiryRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Inquiry $inquiry): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO inquiries (id, name, email, phone, message, created_at)
             VALUES (:id, :name, :email, :phone, :message, :created_at)
             ON CONFLICT(id) DO UPDATE SET
                name = excluded.name,
                email = excluded.email,
                phone = excluded.phone,
                message = excluded.message,
                created_at = excluded.created_at'
        );

        $statement->execute([
            'id' => $inquiry->id(),
            'name' => $inquiry->name(),
            'email' => $inquiry->email(),
            'phone' => $inquiry->phone(),
            'message' => $inquiry->message(),
            'created_at' => $inquiry->createdAt(),
        ]);
    }

    /**
     * @return Inquiry[]
     */
    public function findAll(): array
    {
        $rows = $this->pdo->query('SELECT * FROM inquiries ORDER BY rowid DESC')->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Inquiry
    {
        $phone = isset($row['phone']) && trim((string) $row['phone']) !== ''
            ? (string) $row['phone']
            : null;

        return Inquiry::fromPersistence(
            (string) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            $phone,
            (string) $row['message'],
            (string) $row['created_at']
        );
    }
}
