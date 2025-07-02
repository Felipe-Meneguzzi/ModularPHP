<?php
declare(strict_types=1);

namespace App\Module\RequestLog\Repository;

use App\Core\DB\IDBConnection;
use App\Entity\RequestLogEntity;
use Illuminate\Database\Connection;

interface IRequestLogRepository {
    public function insert(RequestLogEntity $entity);
}

class RequestLogRepository implements IRequestLogRepository {
    private Connection $db;
    private string $table;
    private string $entityClass;

    public function __construct(protected IDBConnection $dbClass) {
        $this->db = $dbClass->getConnection();
        $this->table = 'request_logs';
        $this->entityClass = RequestLogEntity::class;
    }

    public function insert(RequestLogEntity $entity): bool {
        $connectionName = $this->db->getName();

        $entity->setConnection($connectionName);

        $entity->save();

        return true;
    }
}