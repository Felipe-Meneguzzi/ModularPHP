<?php
declare(strict_types=1);

namespace App\Module\User\Service;

use App\Core\Exception\NotFoundException;
use App\Core\Exception\RequiredParamException;
use App\Module\User\Repository\IUserRepository;

interface IGetUserByIdService {
	public function run(string $uuid): array;
}

class GetUserByIdService implements IGetUserByIdService {
	public function __construct(protected IUserRepository $repository) {}

	public function run(string $uuid): array {
        if(trim($uuid) === '') {
            throw new RequiredParamException(['uuid']);
        }

        $entity = $this->repository->getById($uuid);
        if (empty($entity)) {
            throw new NotFoundException('User');
        }

        $oDTO = [
            $entity
        ];

        return [
            'data' => $oDTO
        ];
	}
}