<?php
declare(strict_types = 1);

namespace App\Module\UserType\Service;

use App\Module\UserType\Repository\IUserTypeRepository;
use App\Core\Exception\NotFoundException;

interface IGetAllUserTypesService {
	public function run(): array;
}

class GetAllUserTypesService implements IGetAllUserTypesService {
	public function __construct(protected IUserTypeRepository $repository) {}

	public function run(): array {
		$entitiesArray = $this->repository->getAll();

        if (!$entitiesArray) {
            throw new NotFoundException('User Types');
        }

        return [
            'data' => $entitiesArray
        ];
	}
}