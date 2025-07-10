<?php
declare(strict_types = 1);

namespace App\Module\UserType\Repository;

use App\Core\DB\IDBConnection;
use App\Entity\UserTypeEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connection;

interface IUserTypeRepository {
	public function getAll(): array;
    public function getById(string $uuid): ?Model;
    public function create(Model $entity): Model;
    public function update(Model $entity): Model;
    public function delete(Model $entity): void;
}

class UserTypeRepository implements IUserTypeRepository {
	private Connection $db;
    private string $entityClass;

	public function __construct(protected IDBConnection $dbClass) {
		$this->db = $dbClass->getConnection();
        $this->entityClass = UserTypeEntity::class;
	}

    public function getAll(): array {
        $returnArray = [];
        $entity = new $this->entityClass;
        $entity->setConnection($this->db->getName());

        $collection = $entity->get();

        foreach ($collection as $dbEntity) {
            $returnArray[] = $dbEntity;
        }

        return $returnArray;
    }

    public function getById(string $uuid): ?Model {
        $entity = new $this->entityClass;
        $entity->setConnection($this->db->getName());

        return $entity->where('uuid', $uuid)->first();
    }

    public function create(Model $entity): Model {
        $entity->setConnection($this->db->getName());

        $entity->save();

        return $entity;
    }

    public function update(Model $entity): Model {
        $entity->setConnection($this->db->getName());
        $entity->exists = true;

        $entity->save();

        return $entity;
    }

    public function delete(Model $entity): void {
        $entity->setConnection($this->db->getName());

        $entity->delete();
    }
}