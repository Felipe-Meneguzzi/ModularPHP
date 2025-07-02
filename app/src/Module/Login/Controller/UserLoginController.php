<?php
declare(strict_types = 1);

namespace App\Module\Login\Controller;

use App\Core\Exception\RequiredParamException;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\Login\Service\IUserLoginService;
use OpenApi\Attributes as OA;

class UserLoginController {
    #[OA\Post(
        path: '/login',
        summary: 'Realiza o login do usuário',
        requestBody: new OA\RequestBody(
            description: 'Credenciais de login',
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: 'admin'),
                    new OA\Property(property: 'password', type: 'string', example: 'admin')
                ]
            )
        ),
        tags: ['Login'],
        responses: [
            new OA\Response(response: 200, description: 'Login bem-sucedido, retorna token JWT'),
            new OA\Response(response: 401, description: 'Senha incorreta'),
            new OA\Response(response: 404, description: 'Usuário não encontrado')
        ]
    )]
	public function __construct(protected IUserLoginService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
		$login = $request->body['login'] ?? null;
		$password = $request->body['password'] ?? null;

        if (empty($login) || empty($password)) {
            throw new RequiredParamException(['login', 'password']);
        }

        $iDTO = [
            'login' => $login,
            'password' => $password,
            'ip' => $request->requestIP,
            'userAgent' => $request->userAgent,
            'headers' => $request->headers,
        ];

		$serviceResponse = $this->service->run($iDTO);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}