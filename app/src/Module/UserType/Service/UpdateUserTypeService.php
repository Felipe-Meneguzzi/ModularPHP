<?php
declare(strict_types = 1);

namespace App\Module\UserType\Service;

use App\Core\Exception\NotFoundException;
use App\Entity\UserTypeEntity;
use App\Module\UserType\Repository\IUserTypeRepository;
use App\Module\UserType\Validator\UserTypeValidator;

interface IUpdateUserTypeService {
	public function run(array $iDTO): array;
}

class UpdateUserTypeService implements IUpdateUserTypeService {
	public function __construct(protected IUserTypeRepository $repository) {}

	public function run(array $iDTO): array {
        $originalEntity = $this->repository->getById($iDTO['uuid']);
        if (!$originalEntity) {
            throw new NotFoundException('User Type');
        }

        $userData = [
            'uuid' => $originalEntity->uuid,            //Not allowed to update
            'name' => $iDTO['name'],
        ];

        $userData = UserTypeValidator::validate($userData);

        $user = new UserTypeEntity($userData);
        
		$updatedUser = $this->repository->update($user);

        $oDTO = [
            $updatedUser
        ];

        return [
            'data' => $oDTO,
            'message' => 'User Type updated'
        ];
	}
}