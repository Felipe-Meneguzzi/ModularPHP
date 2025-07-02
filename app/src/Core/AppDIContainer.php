<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\DB\DBConnection;
use App\Core\DB\IDBConnection;
use App\Module\Login\Service\AuthenticateService;
use App\Module\Login\Service\IAuthenticateService;
use App\Module\Login\Service\IUserLoginService;
use App\Module\Login\Service\UserLoginService;
use App\Module\Login\Repository\UserLoginRepository;
use App\Module\Login\Repository\IUserLoginRepository;
use App\Module\RequestLog\Repository\IRequestLogRepository;
use App\Module\RequestLog\Repository\RequestLogRepository;
use App\Module\RequestLog\Service\IRequestLogService;
use App\Module\RequestLog\Service\RequestLogService;
use App\Module\User\Repository\IUserRepository;
use App\Module\User\Repository\UserRepository;
use App\Module\User\Service\CreateUserService;
use App\Module\User\Service\DeleteUserByIdService;
use App\Module\User\Service\GetAllUsersService;
use App\Module\User\Service\GetUserByIdService;
use App\Module\User\Service\ICreateUserService;
use App\Module\User\Service\IDeleteUserByIdService;
use App\Module\User\Service\IGetAllUsersService;
use App\Module\User\Service\IGetUserByIdService;
use App\Module\User\Service\IUpdateUserService;
use App\Module\User\Service\UpdateUserService;
use DI\Container;
use DI\ContainerBuilder;
use function DI\autowire;

class AppDIContainer {
	public static function build(): Container {
		$builder = new ContainerBuilder();

		$builder->addDefinitions([
			IUserLoginService::class => autowire(UserLoginService::class),
			IUserLoginRepository::class => autowire(UserLoginRepository::class),
            IRequestLogService::class => autowire(RequestLogService::class),
            IRequestLogRepository::class => autowire(RequestLogRepository::class),
            IGetAllUsersService::class => autowire(GetAllUsersService::class),
            IUserRepository::class => autowire(UserRepository::class),
            IGetUserByIdService::class => autowire(GetUserByIdService::class),
            IAuthenticateService::class => autowire(AuthenticateService::class),
            ICreateUserService::class =>  autowire(CreateUserService::class),
            IUpdateUserService::class =>  autowire(UpdateUserService::class),
            IDeleteUserByIdService::class =>  autowire(DeleteUserByIdService::class),
		]);

        /********************************************************DATABASE********************************************************/
		$builder->addDefinitions([
			IDBConnection::class => function () {
				return new DBConnection(
					driver:   $_ENV['DB_DRIVER'],
                    host:     $_ENV['DB_HOST'],
                    database: $_ENV['DB_NAME'],
					username: $_ENV['DB_USER'] ?? '',
					password: $_ENV['DB_PASSWORD'] ?? '',
                    charset:  $_ENV['DB_CHARSET'] ?? 'utf8mb4',
				);
			}
		]);

		return $builder->build();
	}
}
