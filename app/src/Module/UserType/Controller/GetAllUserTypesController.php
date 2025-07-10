<?php
declare(strict_types = 1);

namespace App\Module\UserType\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\UserType\Service\IGetAllUserTypesService;

class GetAllUserTypesController {
	public function __construct(protected IGetAllUserTypesService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
		$serviceResponse = $this->service->run();

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}