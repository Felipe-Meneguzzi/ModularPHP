<?php
declare(strict_types=1);

namespace App\Module\User\Service;

use App\Entity\UserEntity;
use App\Module\User\Repository\IUserRepository;
use App\Module\User\Validator\UserValidator;
use Ramsey\Uuid\Uuid;

interface ICreateUserService {
	public function run(array $iDTO): array;
}

class CreateUserService implements ICreateUserService {
	public function __construct(protected IUserRepository $repository) {}

	public function run(array $iDTO): array {
        $uuid = Uuid::uuid4()->toString();

        $userData = UserValidator::validate($iDTO);

        $userData['uuid'] = $uuid;
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        $user = new UserEntity($userData);

		$createdUser = $this->repository->create($user);

        $oDTO = [
            $createdUser
        ];

        return [
            'statusCode' => 201,
            'data' => $oDTO,
            'message' => 'User created'
        ];
	}

    public function validate(array $iDTO): void {
        
    }
}