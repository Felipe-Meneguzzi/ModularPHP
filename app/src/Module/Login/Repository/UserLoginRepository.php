<?php
declare(strict_types=1);

namespace App\Module\Login\Repository;

use App\Core\DB\IDBConnection;
use App\Entity\UserEntity;
use Illuminate\Database\Connection;

interface IUserLoginRepository {
	public function findByLogin(string $login);
}

class UserLoginRepository implements IUserLoginRepository {
	private Connection $db;
    private string $table;
    private string $entityClass;

	public function __construct(protected IDBConnection $dbClass) {
		$this->db = $dbClass->getConnection();
        $this->table = 'users';
        $this->entityClass = UserEntity::class;
	}

	public function findByLogin(string $login): ?UserEntity {
        $data = $this->db->table($this->table)
            ->where('login', $login)
            ->first();

        if (!$data) {
            return null;
        }

        $entity = new $this->entityClass;

        return $entity->newInstance((array) $data, true);
	}
}