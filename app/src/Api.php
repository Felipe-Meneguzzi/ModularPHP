<?php
declare(strict_types=1);

use App\Middleware\AuthenticateMiddleware;
use App\Middleware\RequestLogMiddleware;
use App\Module\Login\Controller\UserLoginController;
use App\Module\User\Controller\CreateUserController;
use App\Module\User\Controller\DeleteUserByIdController;
use App\Module\User\Controller\GetUserByIdController;
use App\Module\User\Controller\UpdateUserController;
use App\Router;
use App\Module\User\Controller\GetAllUsersController;

return function (Router $router) {
    /************************************************************************************************************************************************/
    /************************************************************************************************************************************************/
    /***************************************************ROTAS DENTRO DA APLICAÇÃO, PASSAM PELO LOG***************************************************/
    /************************************************************************************************************************************************/
    /************************************************************************************************************************************************/
    $router->get('/api', function () {
        return new \App\Core\Http\DefaultResponse(statusCode: 200, message: 'API ON :)');
    });

    $router->group(['prefix' => '/api', 'middleware' => [RequestLogMiddleware::class]], function ($router) {

        $router->group(['prefix' => '/login'], function ($router) {

            $router->post(uri: '', handler: [UserLoginController::class, 'run']);

        });

        /************************************************************************************************************************************************/
        /*****************************************************************ROTAS LOGADAS******************************************************************/
        /************************************************************************************************************************************************/

        $router->group(['prefix' => '/auth', 'middleware' => [AuthenticateMiddleware::class]], function ($router) {

            $router->group(['prefix' => '/user'], function ($router) {
                $router->get(uri: '/{uuid}', handler: [GetUserByIdController::class, 'run']);
                $router->get(uri: '', handler: [GetAllUsersController::class, 'run']);
                $router->post(uri: '', handler: [CreateUserController::class, 'run']);
                $router->put(uri: '/{uuid}', handler: [UpdateUserController::class, 'run']);
                $router->delete(uri: '/{uuid}', handler: [DeleteUserByIdController::class, 'run']);
            });

        });

    });
};