<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPhoneToInquiriesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('inquiries')
            ->addColumn('phone', 'string', ['limit' => 40, 'null' => true, 'after' => 'email'])
            ->update();
    }
}
