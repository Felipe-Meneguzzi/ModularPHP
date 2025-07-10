<?php
declare(strict_types = 1);

use App\Middleware\AuthenticateMiddleware;
use App\Middleware\RequestLogMiddleware;
use App\Module\Login\Controller\UserLoginController;
use App\Module\User\Controller\CreateUserController;
use App\Module\User\Controller\DeleteUserByIdController;
use App\Module\User\Controller\GetUserByIdController;
use App\Module\User\Controller\UpdateUserController;
use App\Module\User\Controller\GetAllUsersController;
use App\Module\UserType\Controller\CreateUserTypeController;
use App\Module\UserType\Controller\DeleteUserTypeByIdController;
use App\Module\UserType\Controller\GetUserTypeByIdController;
use App\Module\UserType\Controller\UpdateUserTypeController;
use App\Module\UserType\Controller\GetAllUserTypesController;
use App\Router;

return function (Router $router) {
    /************************************************************************************************************************************************/
    /************************************************************************************************************************************************/
    /***********************************************INSIDE APP ROUTES, GET PASS THE LOG MIDDLEWARE***************************************************/
    /************************************************************************************************************************************************/
    /************************************************************************************************************************************************/
    $router->get('/on', function () {
        return new \App\Core\Http\DefaultResponse(statusCode: 200, message: 'API ON :)');
    });

    $router->group(['prefix' => '', 'middleware' => [RequestLogMiddleware::class]], function ($router) {

        $router->group(['prefix' => '/login'], function ($router) {

            $router->post(uri: '', handler: [UserLoginController::class, 'run']);

        });

        /************************************************************************************************************************************************/
        /*****************************************************************LOGGED ROUTES******************************************************************/
        /************************************************************************************************************************************************/

        $router->group(['prefix' => '/auth', 'middleware' => [AuthenticateMiddleware::class]], function ($router) {

            $router->group(['prefix' => '/user'], function ($router) {
                $router->get(uri: '/{uuid}', handler: [GetUserByIdController::class, 'run']);
                $router->get(uri: '', handler: [GetAllUsersController::class, 'run']);
                $router->post(uri: '', handler: [CreateUserController::class, 'run']);
                $router->put(uri: '/{uuid}', handler: [UpdateUserController::class, 'run']);
                $router->delete(uri: '/{uuid}', handler: [DeleteUserByIdController::class, 'run']);
            });

            $router->group(['prefix' => '/user-type'], function ($router) {
                $router->get(uri: '/{uuid}', handler: [GetUserTypeByIdController::class, 'run']);
                $router->get(uri: '', handler: [GetAllUserTypesController::class, 'run']);
                $router->post(uri: '', handler: [CreateUserTypeController::class, 'run']);
                $router->put(uri: '/{uuid}', handler: [UpdateUserTypeController::class, 'run']);
                $router->delete(uri: '/{uuid}', handler: [DeleteUserTypeByIdController::class, 'run']);
            });

        });

    });
};