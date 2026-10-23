<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Infrastructure\Persistence;

use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use PDO;

final class SqliteMediaRepository implements MediaRepositoryInterface
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Media
    {
        $statement = $this->pdo->prepare('SELECT * FROM media WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /**
     * @return Media[]
     */
    public function findByOwner(string $ownerType, string $ownerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM media
             WHERE owner_type = :owner_type AND owner_id = :owner_id
             ORDER BY position ASC, rowid ASC'
        );
        $statement->execute([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function nextPosition(string $ownerType, string $ownerId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COALESCE(MAX(position), -1) + 1
             FROM media
             WHERE owner_type = :owner_type AND owner_id = :owner_id'
        );
        $statement->execute([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function save(Media $media): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO media
                (id, owner_type, owner_id, type, path, original_filename, mime_type, position, created_at)
             VALUES
                (:id, :owner_type, :owner_id, :type, :path, :original_filename, :mime_type, :position, :created_at)
             ON CONFLICT(id) DO UPDATE SET
                owner_type = excluded.owner_type,
                owner_id = excluded.owner_id,
                type = excluded.type,
                path = excluded.path,
                original_filename = excluded.original_filename,
                mime_type = excluded.mime_type,
                position = excluded.position'
        );

        $statement->execute([
            'id' => $media->id(),
            'owner_type' => $media->ownerType(),
            'owner_id' => $media->ownerId(),
            'type' => $media->type(),
            'path' => $media->path(),
            'original_filename' => $media->originalFilename(),
            'mime_type' => $media->mimeType(),
            'position' => $media->position(),
            'created_at' => $media->createdAt(),
        ]);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM media WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Media
    {
        return Media::fromPersistence(
            (string) $row['id'],
            (string) $row['owner_type'],
            (string) $row['owner_id'],
            (string) $row['type'],
            (string) $row['path'],
            (string) $row['original_filename'],
            (string) $row['mime_type'],
            (int) $row['position'],
            (string) $row['created_at']
        );
    }
}
