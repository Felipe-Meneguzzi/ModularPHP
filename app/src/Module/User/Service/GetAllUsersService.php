<?php
declare(strict_types = 1);

namespace App\Module\User\Service;

use App\Core\Http\PaginationObject;
use App\Module\User\Repository\IUserRepository;

interface IGetAllUsersService {
	public function run(array $paginationData): array;
}

class GetAllUsersService implements IGetAllUsersService {
	public function __construct(protected IUserRepository $repository) {}

	public function run(array $paginationData): array {
        $pagination = PaginationObject::fromArray($paginationData);

		$entitiesArray = $this->repository->getAll($pagination);

        return [
            'data' => $entitiesArray,
            'metadata' => [
                'sent_pagination' => $pagination->toArray()
            ]
        ];
	}
}