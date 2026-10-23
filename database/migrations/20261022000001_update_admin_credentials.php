<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class UpdateAdminCredentials extends AbstractMigration
{
    private const OLD_EMAIL = 'admin@example.com';
    private const NEW_EMAIL = 'admin@msmatafuegos.com';
    private const NEW_PASSWORD = 'Admin1234';

    public function up(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        $hash = (string) password_hash(self::NEW_PASSWORD, PASSWORD_BCRYPT);
        $newId = $this->findIdByEmail($pdo, self::NEW_EMAIL);
        $oldId = $this->findIdByEmail($pdo, self::OLD_EMAIL);

        if ($newId !== null) {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
                ->execute([$hash, $newId]);

            if ($oldId !== null && $oldId !== $newId) {
                $pdo->prepare('DELETE FROM admins WHERE id = ?')->execute([$oldId]);
            }

            return;
        }

        if ($oldId !== null) {
            $pdo->prepare('UPDATE admins SET email = ?, password_hash = ? WHERE id = ?')
                ->execute([self::NEW_EMAIL, $hash, $oldId]);

            return;
        }

        $pdo->prepare(
            'INSERT INTO admins (id, name, email, password_hash, status) VALUES (?, ?, ?, ?, ?)'
        )->execute([
            Uuid::uuid4()->toString(),
            'Admin',
            self::NEW_EMAIL,
            $hash,
            'active',
        ]);
    }

    public function down(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        $newId = $this->findIdByEmail($pdo, self::NEW_EMAIL);

        if ($newId === null) {
            return;
        }

        $pdo->prepare('UPDATE admins SET email = ?, password_hash = ? WHERE id = ?')
            ->execute([
                self::OLD_EMAIL,
                (string) password_hash('admin1234', PASSWORD_BCRYPT),
                $newId,
            ]);
    }

    private function findIdByEmail(PDO $pdo, string $email): ?string
    {
        $statement = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $id = $statement->fetchColumn();

        return is_string($id) && $id !== '' ? $id : null;
    }
}
