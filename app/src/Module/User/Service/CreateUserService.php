<?php
declare(strict_types = 1);

namespace App\Module\User\Service;

use App\Core\Exception\AppException;
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
        $entityData = UserValidator::validate($iDTO);
        
        $uuid = Uuid::uuid4()->toString();

        $entityData['uuid'] = $uuid;
        $entityData['password'] = password_hash($entityData['password'], PASSWORD_DEFAULT);

        $entity = new UserEntity($entityData);

        try {
            $createdEntity = $this->repository->create($entity);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry')) {
                preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $e->getMessage(), $matches);
                $duplicateValue = $matches[1] ?? 'unknown';
                throw new AppException("Duplicate entry for '{$duplicateValue}'. Please choose a different value.", 409);
            }
            throw new AppException('Error creating user: ' . $e->getMessage(), 400);
        }
		
        $oDTO = [
            $createdEntity
        ];

        return [
            'statusCode' => 201,
            'data' => $oDTO,
            'message' => 'User created'
        ];
	}

}