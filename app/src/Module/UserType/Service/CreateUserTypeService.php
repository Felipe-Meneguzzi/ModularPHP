<?php
declare(strict_types = 1);

namespace App\Module\UserType\Service;

use App\Core\Exception\AppException;
use App\Entity\UserTypeEntity;
use App\Module\UserType\Repository\IUserTypeRepository;
use App\Module\UserType\Validator\UserTypeValidator;
use Ramsey\Uuid\Uuid;

interface ICreateUserTypeService {
	public function run(array $iDTO): array;
}

class CreateUserTypeService implements ICreateUserTypeService {
	public function __construct(protected IUserTypeRepository $repository) {}

	public function run(array $iDTO): array {
        $entityData = UserTypeValidator::validate($iDTO);
        
        $uuid = Uuid::uuid4()->toString();

        $entityData['uuid'] = $uuid;

        $entity = new UserTypeEntity($entityData);

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

    public function validate(array $iDTO): void {
        
    }
}