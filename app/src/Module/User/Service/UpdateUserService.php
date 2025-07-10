<?php
declare(strict_types = 1);

namespace App\Module\User\Service;

use App\Core\Exception\NotFoundException;
use App\Entity\UserEntity;
use App\Module\User\Repository\IUserRepository;
use App\Module\User\Validator\UserValidator;

interface IUpdateUserService {
	public function run(array $iDTO): array;
}

class UpdateUserService implements IUpdateUserService {
	public function __construct(protected IUserRepository $repository) {}

	public function run(array $iDTO): array {
        $originalEntity = $this->repository->getById($iDTO['uuid']);
        if (!$originalEntity) {
            throw new NotFoundException('User');
        }

        $userData = [
            'uuid' => $originalEntity->uuid,            //Not allowed to update
            'name' => $iDTO['name'],
            'login' => $originalEntity->login,          //Not allowed to update
            'password' => $originalEntity->password,    //Not allowed to update
            'email' => $originalEntity->email,          //Not allowed to update   
            'phone' => $iDTO['phone'],
            'user_type_uuid' => $iDTO['user_type_uuid'],
            'cpf' => $iDTO['cpf'],
            'building_uuid' => $iDTO['building_uuid'],
            'company_uuid' => $iDTO['company_uuid']
        ];

        $userData = UserValidator::validate($userData);

        $user = new UserEntity($userData);
        
		$updatedUser = $this->repository->update($user);

        $oDTO = [
            $updatedUser
        ];

        return [
            'data' => $oDTO,
            'message' => 'User updated'
        ];
	}
}