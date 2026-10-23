<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMediaTable extends AbstractMigration
{
    public function change(): void
    {
        // Tabla polimorfica: owner_type + owner_id, sin FK.
        $this->table('media', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('owner_type', 'string', ['limit' => 40])
            ->addColumn('owner_id', 'string', ['limit' => 36])
            ->addColumn('type', 'string', ['limit' => 20])
            ->addColumn('path', 'string', ['limit' => 255])
            ->addColumn('original_filename', 'string', ['limit' => 255])
            ->addColumn('mime_type', 'string', ['limit' => 120])
            ->addColumn('position', 'integer', ['default' => 0])
            ->addColumn('created_at', 'string', ['limit' => 30])
            ->addIndex(['owner_type', 'owner_id'])
            ->create();
    }
}
