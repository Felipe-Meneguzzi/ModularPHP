<?php
declare(strict_types = 1);

namespace App\Module\User\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\User\Service\IGetUserByIdService;
use OpenApi\Attributes as OA;

class GetUserByIdController {
    #[OA\Get(
        path: '/auth/user/{uuid}',
        summary: 'Searches for a user by ID',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                description: 'ID of the user to be searched',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: 'c255f364-50f7-11f0-92f8-4af298741892'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 401, description: 'Unauthorized (invalid or missing token)')
        ]
    )]
    public function __construct(protected IGetUserByIdService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $uuid = $request->dynamicParams['uuid'] ?? '';

		$serviceResponse = $this->service->run($uuid);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}