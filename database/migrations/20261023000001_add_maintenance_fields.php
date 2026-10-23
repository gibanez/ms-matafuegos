<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Migration\IrreversibleMigrationException;

/**
 * Adds the maintenance fields without rebuilding the tables.
 *
 * Phinx's SQLite adapter implements addColumn() with the create-copy-drop
 * strategy. Because migrations run inside a transaction, Phinx's
 * "PRAGMA foreign_keys = OFF" has no effect there, so dropping the old table
 * would trigger the ON DELETE rules of the child tables (invoice_lines uses
 * CASCADE; quote_lines/invoice_lines use SET NULL). Native ADD COLUMN has the
 * same resulting schema and avoids touching the rows.
 */
final class AddMaintenanceFields extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('ALTER TABLE invoices ADD COLUMN paid_at VARCHAR(10) NULL');
        $this->execute('CREATE INDEX invoices_paid_at_index ON invoices (paid_at ASC)');

        $this->execute('ALTER TABLE products ADD COLUMN requires_maintenance BOOLEAN_INTEGER NULL DEFAULT 0');
        $this->execute('ALTER TABLE products ADD COLUMN maintenance_days INTEGER NULL DEFAULT 365');
    }

    public function down(): void
    {
        throw new IrreversibleMigrationException(
            'Removing the maintenance columns would rebuild the tables and could delete or unlink invoice/quote lines.'
        );
    }
}
