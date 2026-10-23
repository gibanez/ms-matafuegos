<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateQuotesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('quotes', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('sequence', 'integer')
            ->addColumn('number', 'string', ['limit' => 30])
            ->addColumn('customer_id', 'string', ['limit' => 36])
            ->addColumn('issue_date', 'string', ['limit' => 10])
            ->addColumn('valid_until', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'draft'])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('subtotal_cents', 'biginteger', ['default' => 0])
            ->addColumn('adjustments_total_cents', 'biginteger', ['default' => 0])
            ->addColumn('total_cents', 'biginteger', ['default' => 0])
            ->addColumn('created_at', 'string', ['limit' => 30])
            ->addColumn('updated_at', 'string', ['limit' => 30])
            ->addIndex(['sequence'], ['unique' => true])
            ->addIndex(['number'], ['unique' => true])
            ->addIndex(['customer_id'])
            ->addIndex(['status'])
            ->addIndex(['issue_date'])
            ->addForeignKey('customer_id', 'customers', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();
    }
}
