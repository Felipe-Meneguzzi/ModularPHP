<?php
declare(strict_types = 1);

namespace App\Module\UserType\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Exception\RequiredParamException;
use App\Module\UserType\Repository\IUserTypeRepository;

interface IDeleteUserTypeByIdService {
	public function run(string $uuid): array;
}

class DeleteUserTypeByIdService implements IDeleteUserTypeByIdService {
	public function __construct(protected IUserTypeRepository $repository) {}

	public function run(string $uuid): array {
        if(trim($uuid) === '') {
            throw new RequiredParamException(['uuid']);
        }

        $entity = $this->repository->getById($uuid);

        if (!$entity) {
            throw new NotFoundException('User Type');
        }

        $this->repository->delete($entity);

        return [
            'message' => 'User Type deleted'
        ];
	}
}