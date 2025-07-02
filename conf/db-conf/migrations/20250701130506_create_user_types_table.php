<?php

use Phinx\Migration\AbstractMigration;

class CreateUserTypesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('user_types', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_0900_ai_ci',
            'id' => false,
            'primary_key' => 'uuid'
        ]);
        
        $table->addColumn('uuid', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('name', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->create();
    }
}