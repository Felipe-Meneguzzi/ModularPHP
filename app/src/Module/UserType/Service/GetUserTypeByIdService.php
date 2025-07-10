<?php
declare(strict_types = 1);

namespace App\Module\UserType\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Exception\RequiredParamException;
use App\Module\UserType\Repository\IUserTypeRepository;

interface IGetUserTypeByIdService {
	public function run(string $uuid): array;
}

class GetUserTypeByIdService implements IGetUserTypeByIdService {
	public function __construct(protected IUserTypeRepository $repository) {}

	public function run(string $uuid): array {
        if(trim($uuid) === '') {
            throw new RequiredParamException(['uuid']);
        }

        $entity = $this->repository->getById($uuid);
        if (empty($entity)) {
            throw new NotFoundException('User Type');
        }

        $oDTO = [
            $entity
        ];

        return [
            'data' => $oDTO
        ];
	}
}