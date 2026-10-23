<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateInvoiceAdjustmentsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('invoice_adjustments', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('invoice_id', 'string', ['limit' => 36])
            ->addColumn('description', 'string', ['limit' => 255])
            ->addColumn('kind', 'string', ['limit' => 20])
            ->addColumn('value_type', 'string', ['limit' => 20])
            ->addColumn('value', 'integer')
            ->addColumn('amount_cents', 'biginteger', ['default' => 0])
            ->addColumn('position', 'integer', ['default' => 0])
            ->addIndex(['invoice_id'])
            ->addForeignKey('invoice_id', 'invoices', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
