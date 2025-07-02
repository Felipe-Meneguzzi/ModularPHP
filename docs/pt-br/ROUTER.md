# Sistema de Roteamento da Aplicação

Este documento detalha o funcionamento do sistema de roteamento da nossa aplicação. Ele é um componente leve, poderoso e flexível, projetado para lidar com as requisições HTTP de forma eficiente e organizada, seguindo as melhores práticas do PHP moderno.

## Principais Funcionalidades

- **Roteamento RESTful:** Suporte completo aos verbos HTTP (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
- **Agrupamento de Rotas:** Permite agrupar rotas com prefixos de URL e middlewares em comum.
- **Pipeline de Middlewares:** Suporte a um sistema de middlewares em camadas (estilo *Chain of Responsibility*), onde cada middleware pode processar a requisição antes de passá-la para a próxima camada ou para o controller.
- **Parâmetros Dinâmicos:** Captura de segmentos da URL (ex: `/user/123`).
- **Validação de Parâmetros com Regex:** Permite definir regras de validação para os parâmetros diretamente na definição da rota.
- **Injeção de Dependência (DI):** Totalmente integrado com um container de DI para resolver controllers e seus próprios serviços dependentes.
- **Arquitetura Request-Response:** Segue um fluxo claro onde o resultado final da execução de uma rota é sempre um objeto de resposta.

---

## Como Usar

A seguir, exemplos práticos de como definir e organizar suas rotas.

### Uso Básico

Você pode definir rotas para os principais verbos HTTP. O handler (a ação que executa a rota) pode ser tanto uma **Closure** (função anônima) quanto um **array** no formato `[Controller::class, 'metodo']`.

```php
<?php
// Em seu arquivo de rotas (app/src/Api.php)

use App\Module\User\Controller\UserController;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;

/** @var App\Router $router */

// Rota usando uma Closure como handler
$router->get('/', function (HTTPRequest $request) {
    return new DefaultResponse(['message' => 'API online!']);
});

// Rota usando um Controller
$router->get('/users', [UserController::class, 'getAll']);
$router->post('/users', [UserController::class, 'create']);
```

### Agrupamento de Rotas

Para organizar rotas que compartilham um prefixo de URL ou middlewares, use o método `group()`.

```php
<?php

use App\Module\User\Controller\UserController;
use App\Middleware\AuthenticateMiddleware;

/** @var App\Router $router */

// Todas as rotas dentro deste grupo terão o prefixo /users
// e passarão pelo AuthenticateMiddleware antes de executar.
$router->group([
    'prefix' => '/users',
    'middleware' => [AuthenticateMiddleware::class]
], function ($router) {
    // Rota final: GET /users
    $router->get('', [UserController::class, 'getAll']);
    
    // Rota final: GET /users/45
    $router->get('/{id:\d+}', [UserController::class, 'getById']);
});
```

### Middlewares

Middlewares são classes que atuam como "filtros" para as requisições. Eles seguem um padrão de pipeline: cada middleware recebe a requisição, pode modificá-la e deve passá-la para o próximo middleware na cadeia.

A estrutura de um middleware deve ser:

```php
<?php
namespace App\Middleware;

use App\Core\Http\HTTPRequest;

class ExampleMiddleware
{
    public function handle(HTTPRequest $request, callable $next)
    {
        // 1. Lógica a ser executada ANTES do controller...
        // Ex: verificar autenticação, adicionar um cabeçalho, etc.
        
        if (!auth()->check()) {
            // Pode interromper o fluxo aqui, lançando uma exceção
            // ou retornando uma resposta de erro diretamente.
            throw new \App\Core\Exception\AppException('Não autorizado.', 401);
        }

        // 2. Passa a requisição para a próxima camada (outro middleware ou o controller)
        $response = $next($request);

        // 3. Lógica a ser executada DEPOIS do controller...
        // Ex: modificar a resposta final, adicionar logs, etc.
        
        // 4. Retorna a resposta final
        return $response;
    }
}
```

Para aplicar um middleware, passe-o no array de middlewares da rota ou do grupo:
`$router->get('/profile', [ProfileController::class, 'show'], ['middleware' => [AuthenticateMiddleware::class]]);`

---

## Parâmetros de Rota e Validação com Regex

É possível capturar partes dinâmicas da URL e validar seu formato usando Expressões Regulares (Regex).

### Sintaxe

- **Parâmetro Simples:** `{nome}` - Usa uma regex padrão (`[a-zA-Z0-9_-]+`).
- **Parâmetro com Regex Customizada:** `{nome:regex}` - Usa a regex que você fornecer.

### Padrões de Regex Comuns

Aqui está uma tabela com exemplos úteis para validar parâmetros de rota diretamente.

| Objetivo                                          | Regex                                                | Exemplo de Uso                                                      |
|---------------------------------------------------|------------------------------------------------------|---------------------------------------------------------------------|
| Qualquer número (inclui 0)                        | `\d+`                                                | `/posts/{id:\d+}`                                                   |
| **Número inteiro positivo (sem 0)**               | `[1-9]\d*`                                           | `/users/{id:[1-9]\d*}`                                              |
| Número (inclui 0, sem zeros à esquerda)           | `[1-9]\d*\|0`                                        | `/items/{id:[1-9]\d*\|0}`                                           |
| Slug (letras, números e traços)                   | `[a-z0-9-]+`                                         | `/products/{slug:[a-z0-9-]+}`                                       |
| Apenas letras                                     | `[a-zA-Z]+`                                          | `/category/{name:[a-zA-Z]+}`                                        |
| UUID (formato padrão)                             | `[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}` | `/orders/{uuid:[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}}` |
| Nome de arquivo com extensão específica (ex: pdf) | `.+\.pdf`                                            | `/files/{filename:.+\.pdf}`                                         |

---

## Injeção de Dependência e Respostas

### Controllers

O router utiliza o container de Injeção de Dependência para criar as instâncias dos controllers. Isso significa que seus controllers podem receber outras classes (serviços, repositórios, etc.) em seu construtor, e elas serão resolvidas automaticamente.

```php
<?php
namespace App\Module\User\Controller;

use App\Module\User\Service\IUserService;

class UserController
{
    // O UserService será injetado automaticamente pelo container de DI.
    public function __construct(protected IUserService $userService) {}

    public function getAll() {
        // ...
    }
}
```

### Retornando Respostas

Todos os handlers de rota (sejam métodos de controller ou Closures) **devem** retornar uma instância de `App\Core\Http\DefaultResponse`. O router se encarrega de pegar este objeto e enviar a resposta HTTP apropriada para o cliente.

```php
<?php
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;

class UserController
{
    // ...

    public function getById(HTTPRequest $request): DefaultResponse
    {
        $id = $request->dynamicParams['id'];
        $serviceResponse = $this->userService->findUser($id);
        
        return new DefaultResponse($serviceResponse);
    }
}
```