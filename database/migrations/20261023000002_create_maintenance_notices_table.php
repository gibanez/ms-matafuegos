<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMaintenanceNoticesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('maintenance_notices', ['id' => false, 'primary_key' => 'invoice_line_id'])
            ->addColumn('invoice_line_id', 'string', ['limit' => 36])
            ->addColumn('notified_at', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('snoozed_until', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('created_at', 'string', ['limit' => 30])
            ->addColumn('updated_at', 'string', ['limit' => 30])
            ->addForeignKey('invoice_line_id', 'invoice_lines', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
