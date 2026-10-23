<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('products', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('name', 'string', ['limit' => 190])
            ->addColumn('slug', 'string', ['limit' => 140])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('sku', 'string', ['limit' => 60, 'null' => true])
            ->addColumn('category_id', 'string', ['limit' => 36, 'null' => true])
            ->addColumn('provider_id', 'string', ['limit' => 36, 'null' => true])
            ->addColumn('price_cents', 'biginteger', ['default' => 0])
            ->addColumn('show_price', 'boolean', ['default' => true])
            ->addColumn('stock', 'integer', ['default' => 0])
            ->addColumn('min_stock', 'integer', ['default' => 0])
            ->addColumn('featured', 'boolean', ['default' => false])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'active'])
            ->addColumn('created_at', 'string', ['limit' => 30])
            ->addColumn('updated_at', 'string', ['limit' => 30])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['sku'], ['unique' => true])
            ->addIndex(['category_id'])
            ->addIndex(['provider_id'])
            ->addIndex(['featured'])
            ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('provider_id', 'providers', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}
