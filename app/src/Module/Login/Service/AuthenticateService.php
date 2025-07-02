<?php
declare(strict_types=1);

namespace App\Module\Login\Service;

use App\ValueObject\JWTToken;

interface IAuthenticateService {
	public function run(string $token): void;
}

class AuthenticateService implements IAuthenticateService {
	public function __construct() {}

	public function run(string $token): void {
        $decoded = JWTToken::decode($token);
	}
}