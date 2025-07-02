<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Http\HTTPRequest;
use App\Module\Login\Service\IAuthenticateService;

class AuthenticateMiddleware {
    public function __construct(protected IAuthenticateService $service) {}

    public function handle(HTTPRequest $request, callable $next) {
        $token = $request->headers['Authorization'] ?? '';

        $this->service->run($token);

        return $next($request);
    }

}