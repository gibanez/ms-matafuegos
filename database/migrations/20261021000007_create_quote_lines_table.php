<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateQuoteLinesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('quote_lines', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('quote_id', 'string', ['limit' => 36])
            ->addColumn('product_id', 'string', ['limit' => 36, 'null' => true])
            ->addColumn('description', 'string', ['limit' => 255])
            ->addColumn('quantity', 'integer')
            ->addColumn('unit_price_cents', 'biginteger', ['default' => 0])
            ->addColumn('line_total_cents', 'biginteger', ['default' => 0])
            ->addColumn('position', 'integer', ['default' => 0])
            ->addIndex(['quote_id'])
            ->addIndex(['product_id'])
            ->addForeignKey('quote_id', 'quotes', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('product_id', 'products', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
