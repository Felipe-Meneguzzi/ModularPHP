<?php
declare(strict_types = 1);

namespace App\Module\UserType\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\UserType\Service\IGetUserTypeByIdService;

class GetUserTypeByIdController {
    public function __construct(protected IGetUserTypeByIdService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $uuid = $request->dynamicParams['uuid'] ?? '';

		$serviceResponse = $this->service->run($uuid);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}