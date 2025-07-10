<?php
declare(strict_types = 1);

namespace App;

use App\Core\Exception\AppException;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;
use DI\Container;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'A robust and scalable API built with modern PHP best practices.',
    title: 'PHP API Template',
    contact: new OA\Contact(email: 'admin@gmail.com')
)]
#[OA\Server(
    url: 'http://localhost:8080/api',
    description: 'Development Server'
)]
#[OA\Server(
    url: 'https://api.example.com',
    description: 'Production Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: "Insert JWT token in 'Bearer {token}' format",
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class Router {
    protected array $routes = [];
    protected array $groupStack = [];

    #[OA\Get(
        path: '/',
        summary: 'Verifica o status da API',
        tags: ['API'],
        responses: [
            new OA\Response(response: '200', description: 'Api On'),
            new OA\Response(response: '500', description: 'Api with problem')
        ]
    )]
    public function __construct(
        protected readonly HTTPRequest $request,
        protected readonly Container $container
    ) {

    }

    public function group(array $attributes, callable $callback): void {
        $this->groupStack[] = $attributes;

        $callback($this);

        array_pop($this->groupStack);
    }

    public function register(string $uri, string $method, $handler, array $middleware = [], array $controllerParams = []): void {
        $prefix = '';
        $groupMiddleware = [];

        if (!empty($this->groupStack)) {
            foreach ($this->groupStack as $group) {
                $prefix .= $group['prefix'] ?? '';
                $groupMiddleware = array_merge($groupMiddleware, $group['middleware'] ?? []);
            }
        }

        $finalMiddleware = array_unique(array_merge($groupMiddleware, $middleware));

        $this->routes[strtoupper($method)][$prefix . $uri] = [
            'handler' => $handler,
            'controllerParams' => $controllerParams,
            'middleware' => $finalMiddleware
        ];
    }

    public function get(string $uri, array|callable $handler, array $middlewares = [], array $controllerParams = []): void {
        $this->register($uri, 'GET', $handler, $middlewares ?? [], $controllerParams);
    }

    public function post(string $uri, array|callable $handler, array $middlewares = [], array $controllerParams = []): void {
        $this->register($uri, 'POST', $handler, $middlewares ?? [], $controllerParams);
    }

    public function put(string $uri, array|callable $handler, array $middlewares = [], array $controllerParams = []): void {
        $this->register($uri, 'PUT', $handler, $middlewares ?? [], $controllerParams);
    }

    public function patch(string $uri, array|callable $handler, array $middlewares = [], array $controllerParams = []): void {
        $this->register($uri, 'PATCH', $handler, $middlewares ?? [], $controllerParams);
    }

    public function delete(string $uri, array|callable $handler, array $middlewares = [], array $controllerParams = []): void {
        $this->register($uri, 'DELETE', $handler, $middlewares ?? [], $controllerParams);
    }

    public function dispatch(): DefaultResponse {
        $method = strtoupper($this->request->method);
        $uri = parse_url($this->request->uri, PHP_URL_PATH);

        // Normalizes the URI by removing the trailing slash, if any,
        // but does not touch the root route "/".
        if (strlen($uri) > 1) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes[$method] ?? [] as $routeUri => $route) {
            // 1. More powerful regex to find parameters and their custom rules
            // Ex: for /user/{id:\d+}, $matches[0][1] will be 'id' and $matches[0][2] will be '\d+'
            preg_match_all('/\{([a-zA-Z0-9_-]+)(?::([^\}]+))?\}/', $routeUri, $matches, PREG_SET_ORDER);

            $paramKeys = [];
            $pattern = $routeUri;

            // 2. Loop to build the final regex pattern
            foreach ($matches as $match) {
                $placeholder = $match[0]; // O placeholder completo, ex: {id:\d+}
                $keyName     = $match[1]; // O nome da chave, ex: id

                // If a custom regex was defined (match[2]), use it.
                // Otherwise, use the default pattern.
                $customRegex = $match[2] ?? '[a-zA-Z0-9_-]+';

                $paramKeys[] = $keyName;

                // 3. Replaces the placeholder with the capture regex in the pattern string
                // Pay attention to the use of preg_quote for the placeholder, ensuring that '{' and '}'
                // are treated as literal text in the replacement.
                $pattern = str_replace($placeholder, "($customRegex)", $pattern);
            }

            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove o primeiro elemento que é a string completa da URL

                if (count($paramKeys) !== count($matches)) {
                    //todo LOG HERE FOR ERROR
                    continue;
                }

                // Combines the extracted keys with the corresponding values
                // Se houver mais valores do que chaves (ou vice-versa), array_combine lidará com isso
                $this->request->dynamicParams = array_combine($paramKeys, $matches);

                return $this->executePipeline($route['handler'], $route['middleware'], $route['controllerParams']);
            }
        }

        throw new AppException("Route not found", 404);
    }

    protected function executePipeline(array|callable $handler, array $middlewares, array $controllerParams): DefaultResponse {
        // The "core" of the onion/pipeline: the final action that calls the controller.
        $coreAction = function (HTTPRequest $request) use ($handler, $controllerParams) {
            return $this->callAction($handler, $controllerParams);
        };

        // We reverse the middleware array to build the chain from outside in.
        $reversedMiddleware = array_reverse($middlewares);

        // We use array_reduce to wrap each layer of the onion in the previous one.
        $pipeline = array_reduce(
            $reversedMiddleware,
            function ($next, $middlewareClass) {
                // Creates a new function that calls the handle of the current middleware,
                // passing the next layer ($next) as its callable.
                return function (HTTPRequest $request) use ($middlewareClass, $next) {
                    $middlewareInstance = $this->container->get($middlewareClass);
                    return $middlewareInstance->handle($request, $next);
                };
            },
            $coreAction // The initial value is the call to our controller.
        );

        // Executes the complete middleware chain, starting from the outermost layer.
        return call_user_func($pipeline, $this->request);
    }

    protected function callAction($handler, array $params = []): DefaultResponse {
        // Checks if the handler is the [class, method] array
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (class_exists($controllerClass)) {
                $controllerInstance = $this->container->get($controllerClass);

                if (method_exists($controllerInstance, $method)) {
                    return $controllerInstance->$method($this->request, $params);
                }
            }
        }

        // If the handler is a Closure/anonymous function
        if (is_callable($handler)) {
            return $handler($this->request, $params);
        }

        throw new AppException("Invalid handler for the route.", 500);
    }
}