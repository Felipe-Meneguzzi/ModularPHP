<?php
declare(strict_types = 1);

namespace App\Module\User\Repository;

use App\Core\DB\IDBConnection;
use App\Core\Http\PaginationObject;
use App\Entity\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connection;

interface IUserRepository {
	public function getAll(PaginationObject $pagination): array;
    public function getById(string $uuid): ?Model;
    public function create(Model $entity): Model;
    public function update(Model $entity): Model;
    public function delete(Model $entity): void;
}

class UserRepository implements IUserRepository {
	private Connection $db;
    private string $entityClass;

	public function __construct(protected IDBConnection $dbClass) {
		$this->db = $dbClass->getConnection();
        $this->entityClass = UserEntity::class;
	}

    public function getAll(PaginationObject $pagination): array {
        $entity = new $this->entityClass;
        $entity->setConnection($this->db->getName());

        $query = $pagination->generateQueryFromPagination($entity);

        return $query->get()->toArray();
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