<?php
declare(strict_types = 1);

namespace App\Module\UserType\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\UserType\Service\ICreateUserTypeService;

class CreateUserTypeController {
    public function __construct(protected ICreateUserTypeService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $iDTO = [
            'name' => $request->body['name'] ?? null
        ];

		$serviceResponse = $this->service->run($iDTO);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}