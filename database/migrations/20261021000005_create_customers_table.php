<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('customers', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 190])
            ->addColumn('tax_id', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 190, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addColumn('created_at', 'string', ['limit' => 30])
            ->addColumn('updated_at', 'string', ['limit' => 30])
            ->addIndex(['tax_id'], ['unique' => true])
            ->create();
    }
}
