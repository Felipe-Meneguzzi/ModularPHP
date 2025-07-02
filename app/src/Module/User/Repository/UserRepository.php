<?php
declare(strict_types=1);

namespace App\Module\User\Repository;

use App\Core\DB\IDBConnection;
use App\Entity\UserEntity;
use Illuminate\Database\Connection;

interface IUserRepository {
	public function getAll(): array;
    public function getById(string $uuid): ?UserEntity;
    public function create(UserEntity $userEntity): UserEntity;
    public function update(UserEntity $userEntity): UserEntity;
    public function delete(UserEntity $userEntity): void;
}

class UserRepository implements IUserRepository {
	private Connection $db;
    private string $entityClass;

	public function __construct(protected IDBConnection $dbClass) {
		$this->db = $dbClass->getConnection();
        $this->entityClass = UserEntity::class;
	}

    public function getAll(): array {
        $returnArray = [];
        $entity = new $this->entityClass;
        $entity->setConnection($this->db->getName());

        $collection = $entity->get();

        foreach ($collection as $userEntity) {
            $returnArray[] = $userEntity;
        }

        return $returnArray;
    }

    public function getById(string $uuid): ?UserEntity {
        $entity = new $this->entityClass;
        $entity->setConnection($this->db->getName());

        return $entity->where('uuid', $uuid)->first();
    }

    public function create(UserEntity $entity): UserEntity {
        $entity->setConnection($this->db->getName());

        $entity->save();

        return $entity;
    }

    public function update(UserEntity $entity): UserEntity {
        $entity->setConnection($this->db->getName());
        $entity->exists = true;

        $entity->save();

        return $entity;
    }

    public function delete(UserEntity $entity): void {
        $entity->setConnection($this->db->getName());

        $entity->delete();
    }
}