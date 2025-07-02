<?php
declare(strict_types=1);

namespace App;

use App\Core\Exception\AppException;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;
use DI\Container;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Uma API robusta e escalável construída com as melhores práticas do PHP moderno.',
    title: 'API PHP Template',
    contact: new OA\Contact(email: 'admin@gmail.com')
)]
#[OA\Server(
    url: 'http://localhost:8180/api',
    description: 'Servidor de Desenvolvimento'
)]
#[OA\Server(
    url: 'https://api.example.com',
    description: 'Servidor de Produção'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: "Insira o token JWT no formato 'Bearer {token}'",
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

        // Normaliza a URI removendo a barra final, se houver,
        // mas não mexe na rota raiz "/".
        if (strlen($uri) > 1) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes[$method] ?? [] as $routeUri => $route) {
            // 1. Regex mais poderosa para encontrar parâmetros e suas regras customizadas
            // Ex: para /user/{id:\d+}, $matches[0][1] será 'id' e $matches[0][2] será '\d+'
            preg_match_all('/\{([a-zA-Z0-9_-]+)(?::([^\}]+))?\}/', $routeUri, $matches, PREG_SET_ORDER);

            $paramKeys = [];
            $pattern = $routeUri;

            // 2. Loop para construir o padrão regex final
            foreach ($matches as $match) {
                $placeholder = $match[0]; // O placeholder completo, ex: {id:\d+}
                $keyName     = $match[1]; // O nome da chave, ex: id

                // Se uma regex customizada foi definida (match[2]), use-a.
                // Caso contrário, use o padrão default.
                $customRegex = $match[2] ?? '[a-zA-Z0-9_-]+';

                $paramKeys[] = $keyName;

                // 3. Substitui o placeholder pela regex de captura na string do padrão
                // Atenção ao uso de preg_quote para o placeholder, garantindo que os '{' e '}'
                // sejam tratados como texto literal na substituição.
                $pattern = str_replace($placeholder, "($customRegex)", $pattern);
            }

            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove o primeiro elemento que é a string completa da URL

                if (count($paramKeys) !== count($matches)) {
                    //todo LOG AQUI DE ERRO
                    continue;
                }

                // Combina as chaves extraídas com os valores correspondentes
                // Se houver mais valores do que chaves (ou vice-versa), array_combine lidará com isso
                $this->request->dynamicParams = array_combine($paramKeys, $matches);

                return $this->executePipeline($route['handler'], $route['middleware'], $route['controllerParams']);
            }
        }

        throw new AppException("Route not found", 404);
    }

    protected function executePipeline(array|callable $handler, array $middlewares, array $controllerParams): DefaultResponse {
        // O "núcleo" da cebola/pipeline: a ação final que chama o controller.
        $coreAction = function (HTTPRequest $request) use ($handler, $controllerParams) {
            return $this->callAction($handler, $controllerParams);
        };

        // Invertemos o array de middlewares para construir a cadeia de fora para dentro.
        $reversedMiddleware = array_reverse($middlewares);

        // Usamos array_reduce para envolver cada camada da cebola na anterior.
        $pipeline = array_reduce(
            $reversedMiddleware,
            function ($next, $middlewareClass) {
                // Cria uma nova função que chama o handle do middleware atual,
                // passando a próxima camada ($next) como seu callable.
                return function (HTTPRequest $request) use ($middlewareClass, $next) {
                    $middlewareInstance = $this->container->get($middlewareClass);
                    return $middlewareInstance->handle($request, $next);
                };
            },
            $coreAction // O valor inicial é a chamada do nosso controller.
        );

        // Executa a cadeia de middlewares completa, começando pela camada mais externa.
        return call_user_func($pipeline, $this->request);
    }

    protected function callAction($handler, array $params = []): DefaultResponse {
        // Verifica se o handler é o array [classe, método]
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (class_exists($controllerClass)) {
                $controllerInstance = $this->container->get($controllerClass);

                if (method_exists($controllerInstance, $method)) {
                    return $controllerInstance->$method($this->request, $params);
                }
            }
        }

        // Se o handler for uma Closure/função anônima
        if (is_callable($handler)) {
            return $handler($this->request, $params);
        }

        throw new AppException("Invalid handler for the route.", 500);
    }
}