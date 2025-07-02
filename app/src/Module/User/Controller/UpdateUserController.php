<?php
declare(strict_types = 1);

namespace App\Module\User\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\User\Service\IUpdateUserService;

class UpdateUserController {
    public function __construct(protected IUpdateUserService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $iDTO = [
            'uuid' => $request->dynamicParams['uuid'] ?? null,
            'name' => $request->body['name'] ?? null,
            'phone' => $request->body['phone'] ?? null,
            'user_type_uuid' => $request->body['user_type_uuid'] ?? null,
            'cpf' => $request->body['cpf'] ?? null
        ];

		$serviceResponse = $this->service->run($iDTO);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}