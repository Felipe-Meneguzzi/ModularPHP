<?php
declare(strict_types = 1);

namespace App\Middleware;

use App\Core\Http\HTTPRequest;
use App\Module\Login\Service\IAuthenticateService;

class AuthenticateMiddleware {
    public function __construct(protected IAuthenticateService $service) {}

    public function handle(HTTPRequest $request, callable $next) {
        // Check if the Authorization header is present
        if (isset($request->headers['Authorization'])) {
            $token = $request->headers['Authorization'];
        }
        else if (isset($request->headers['authorization'])) {
            $token = $request->headers['authorization'];
        }
        else {
            $token = '';
        } 
        $this->service->run($token);

        return $next($request);
    }

}