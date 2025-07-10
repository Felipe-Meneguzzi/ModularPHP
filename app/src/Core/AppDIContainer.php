<?php
declare(strict_types = 1);

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
use App\Module\UserType\Repository\IUserTypeRepository;
use App\Module\UserType\Repository\UserTypeRepository;
use App\Module\UserType\Service\CreateUserTypeService;
use App\Module\UserType\Service\DeleteUserTypeByIdService;
use App\Module\UserType\Service\GetAllUserTypesService;
use App\Module\UserType\Service\GetUserTypeByIdService;
use App\Module\UserType\Service\ICreateUserTypeService;
use App\Module\UserType\Service\IDeleteUserTypeByIdService;
use App\Module\UserType\Service\IGetAllUserTypesService;
use App\Module\UserType\Service\IGetUserTypeByIdService;
use App\Module\UserType\Service\IUpdateUserTypeService;
use App\Module\UserType\Service\UpdateUserTypeService;
use DI\Container;
use DI\ContainerBuilder;
use function DI\autowire;

class AppDIContainer {
	public static function build(): Container {
		$builder = new ContainerBuilder();

		$builder->addDefinitions([
            //RequestLog Module
            IRequestLogService::class => autowire(RequestLogService::class),
            IRequestLogRepository::class => autowire(RequestLogRepository::class),

            //Login Module
			IUserLoginService::class => autowire(UserLoginService::class),
			IUserLoginRepository::class => autowire(UserLoginRepository::class),
            IAuthenticateService::class => autowire(AuthenticateService::class),

            //User Module
            IUserRepository::class => autowire(UserRepository::class),
            IGetAllUsersService::class => autowire(GetAllUsersService::class),
            IGetUserByIdService::class => autowire(GetUserByIdService::class),
            ICreateUserService::class =>  autowire(CreateUserService::class),
            IUpdateUserService::class =>  autowire(UpdateUserService::class),
            IDeleteUserByIdService::class =>  autowire(DeleteUserByIdService::class),

            //UserType Module
            IUserTypeRepository::class => autowire(UserTypeRepository::class),
            IGetAllUserTypesService::class => autowire(GetAllUserTypesService::class),
            IGetUserTypeByIdService::class => autowire(GetUserTypeByIdService::class),
            ICreateUserTypeService::class =>  autowire(CreateUserTypeService::class),
            IUpdateUserTypeService::class =>  autowire(UpdateUserTypeService::class),
            IDeleteUserTypeByIdService::class =>  autowire(DeleteUserTypeByIdService::class),

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
