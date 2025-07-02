<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('users', [
            'collation' => 'utf8mb4_0900_ai_ci',
            'engine' => 'InnoDB',
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
            ->addColumn('login', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('email', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('phone', 'string', [
                'limit' => 50,
                'null' => true
            ])
            ->addColumn('user_type_uuid', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('cpf', 'string', [
                'limit' => 20,
                'null' => false
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false
            ])
            ->addColumn('updated_at', 'timestamp', [
                  'default' => 'CURRENT_TIMESTAMP',
                  'update' => 'CURRENT_TIMESTAMP'
            ])
            ->addIndex(['login'], ['unique' => true, 'name' => 'users_unique'])
            ->addIndex(['email'], ['unique' => true, 'name' => 'users_unique_1'])
            ->addForeignKey('user_type_uuid', 'user_types', 'uuid', [
                'constraint' => 'users_user_types_FK',
                'delete' => 'RESTRICT',
                'update' => 'RESTRICT'
            ])
            ->create();
    }
}