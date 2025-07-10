<?php
declare(strict_types = 1);

namespace App\Module\User\Controller;

use App\Core\Http\DefaultResponse;
use App\Core\Http\HttpRequest;
use App\Module\User\Service\IGetAllUsersService;

class GetAllUsersController {
	public function __construct(protected IGetAllUsersService $service) {}

	public function run(HTTPRequest $request): DefaultResponse {
        $paginationData = [
            'page' => $request->queryParams['page'] ?? null,
            'limit' => $request->queryParams['limit'] ?? null,
            'search' => $request->queryParams['search'] ?? null,
            'like' => $request->queryParams['like'] ?? null,
            'sort' => $request->queryParams['sort'] ?? null,
            'eqor' => $request->queryParams['eqor'] ?? null,
            'eqand' => $request->queryParams['eqand'] ?? null,
            'null' => $request->queryParams['null'] ?? null,
            'not_null' => $request->queryParams['not_null'] ?? null
        ];

		$serviceResponse = $this->service->run($paginationData);

        return DefaultResponse::getDefaultResponse($serviceResponse);
	}

}