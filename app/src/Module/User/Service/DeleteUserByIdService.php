<?php
declare(strict_types = 1);

namespace App\Module\User\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Exception\RequiredParamException;
use App\Module\User\Repository\IUserRepository;

interface IDeleteUserByIdService {
	public function run(string $uuid): array;
}

class DeleteUserByIdService implements IDeleteUserByIdService {
	public function __construct(protected IUserRepository $repository) {}

	public function run(string $uuid): array {
        if(trim($uuid) === '') {
            throw new RequiredParamException(['uuid']);
        }

        $entity = $this->repository->getById($uuid);

        if (!$entity) {
            throw new NotFoundException('User');
        }

        $this->repository->delete($entity);

        return [
            'message' => 'User deleted'
        ];
	}
}