<?php
declare(strict_types = 1);

namespace App\Module\Login\Service;

use App\Core\Exception\AppException;
use App\Module\Login\Repository\IUserLoginRepository;
use App\ValueObject\JWTToken;

interface IUserLoginService {
	public function run(array $iDTO): array;
}

class UserLoginService implements IUserLoginService {
	public function __construct(protected IUserLoginRepository $repository) {}

	public function run(array $iDTO): array {
		$user = $this->repository->findByLogin($iDTO['login']);

        if (!$user) {
            throw new AppException('User not found', 404);
        }

        if (!password_verify($iDTO['password'], $user->password)) {
            throw new AppException('Wrong password', 401);
        }

        $payload = [
            'user' => $user->toArray(),
            'original_request' => [
                'ip' => $iDTO['ip'],
                'user_agent' => $iDTO['userAgent']
            ]
        ];

        $token = JWTToken::fromPayload($payload);
        
        return [
            'data' => [
                'jwt_token' => $token->__toString(),
                'user' => $user
            ],
            'message' => 'Login successful'
        ];
	}

}