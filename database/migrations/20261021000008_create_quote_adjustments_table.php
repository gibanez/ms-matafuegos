<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateQuoteAdjustmentsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('quote_adjustments', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('quote_id', 'string', ['limit' => 36])
            ->addColumn('description', 'string', ['limit' => 255])
            ->addColumn('kind', 'string', ['limit' => 20])
            ->addColumn('value_type', 'string', ['limit' => 20])
            ->addColumn('value', 'integer')
            ->addColumn('amount_cents', 'biginteger', ['default' => 0])
            ->addColumn('position', 'integer', ['default' => 0])
            ->addIndex(['quote_id'])
            ->addForeignKey('quote_id', 'quotes', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
