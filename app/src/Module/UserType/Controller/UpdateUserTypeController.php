<?php
declare(strict_types = 1);

namespace App\Module\UserType\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\UserType\Service\IUpdateUserTypeService;

class UpdateUserTypeController {
    public function __construct(protected IUpdateUserTypeService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $iDTO = [
            'uuid' => $request->dynamicParams['uuid'] ?? null,
            'name' => $request->body['name'] ?? null
        ];

		$serviceResponse = $this->service->run($iDTO);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}