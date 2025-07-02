<?php

use Phinx\Migration\AbstractMigration;

class CreateRequestLogsTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('request_logs', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_0900_ai_ci'
        ]);
        
        $table->addColumn('user_id', 'integer', [
                'signed' => false,
                'null' => false
            ])
            ->addColumn('uri', 'string', [
                'limit' => 255,
                'null' => false
            ])
            ->addColumn('method', 'string', [
                'limit' => 20,
                'null' => false
            ])
            ->addColumn('headers', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG,
                'null' => false
            ])
            ->addColumn('body', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG,
                'null' => true
            ])
            ->addColumn('cookies', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG,
                'null' => true
            ])
            ->addColumn('agent', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('time', 'datetime', [
                'null' => false
            ])
            ->addColumn('ip', 'string', [
                'limit' => 40,
                'null' => false
            ])
            ->addIndex(['user_id'])
            ->addIndex(['time'])
            ->addIndex(['method'])
            ->create();
    }
}