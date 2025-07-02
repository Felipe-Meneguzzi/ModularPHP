# Application Routing System

This document details the operation of our application's routing system. It is a lightweight, powerful, and flexible component designed to handle HTTP requests efficiently and organized, following the best practices of modern PHP.

## Key Features

- **RESTful Routing:** Full support for HTTP verbs (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
- **Route Grouping:** Allows grouping routes with common URL prefixes and middlewares.
- **Middleware Pipeline:** Support for a layered middleware system (Chain of Responsibility style), where each middleware can process the request before passing it to the next layer or to the controller.
- **Dynamic Parameters:** Capture URL segments (e.g., `/user/123`).
- **Parameter Validation with Regex:** Allows defining validation rules for parameters directly in the route definition.
- **Dependency Injection (DI):** Fully integrated with a DI container to resolve controllers and their own dependent services.
- **Request-Response Architecture:** Follows a clear flow where the final result of a route execution is always a response object.

---

## How to Use

Below are practical examples of how to define and organize your routes.

### Basic Usage

You can define routes for the main HTTP verbs. The handler (the action that executes the route) can be either a **Closure** (anonymous function) or an **array** in the format `[Controller::class, 'method']`.

```php
<?php
// In your routes file (app/src/Api.php)

use App\Module\User\Controller\UserController;
use App\Core\Http\DefaultResponse;
use App\Core\Http\HTTPRequest;

/** @var App\Router $router */

// Route using a Closure as a handler
$router->get('/', function (HTTPRequest $request) {
    return new DefaultResponse(['message' => 'API online!']);
});

// Route using a Controller
$router->get('/users', [UserController::class, 'getAll']);
$router->post('/users', [UserController::class, 'create']);
```

### Route Grouping

To organize routes that share a URL prefix or middlewares, use the `group()` method.

```php
<?php

use App\Module\User\Controller\UserController;
use App\Middleware\AuthenticateMiddleware;

/** @var App\Router $router */

// All routes within this group will have the /users prefix
// and will pass through the AuthenticateMiddleware before executing.
$router->group([
    'prefix' => '/users',
    'middleware' => [AuthenticateMiddleware::class]
], function ($router) {
    // Final route: GET /users
    $router->get('', [UserController::class, 'getAll']);
    
    // Final route: GET /users/45
    $router->get('/{id:\d+}', [UserController::class, 'getById']);
});
```

### Middlewares

Middlewares are classes that act as "filters" for requests. They follow a pipeline pattern: each middleware receives the request, can modify it, and must pass it to the next middleware in the chain.

The structure of a middleware should be:

```php
<?php
namespace App\Middleware;

use App\Core\Http\HTTPRequest;

class ExampleMiddleware
{
    public function handle(HTTPRequest $request, callable $next)
    {
        // 1. Logic to be executed BEFORE the controller...
        // Ex: check authentication, add a header, etc.
        
        if (!auth()->check()) {
            // You can interrupt the flow here, throwing an exception
            // or returning an error response directly.
            throw new \App\Core\Exception\AppException('Unauthorized.', 401);
        }

        // 2. Pass the request to the next layer (another middleware or the controller)
        $response = $next($request);

        // 3. Logic to be executed AFTER the controller...
        // Ex: modify the final response, add logs, etc.
        
        // 4. Return the final response
        return $response;
    }
}
```

To apply a middleware, pass it in the middlewares array of the route or group:
`$router->get('/profile', [ProfileController::class, 'show'], ['middleware' => [AuthenticateMiddleware::class]]);`

---

## Route Parameters and Regex Validation

It is possible to capture dynamic parts of the URL and validate their format using Regular Expressions (Regex).

### Syntax

- **Simple Parameter:** `{name}` - Uses a default regex (`[a-zA-Z0-9_-]+`).
- **Parameter with Custom Regex:** `{name:regex}` - Uses the regex you provide.

### Common Regex Patterns

Here is a table with useful examples for validating route parameters directly.

| Objective                                          | Regex                                                | Example Usage                                                      |
|---------------------------------------------------|------------------------------------------------------|---------------------------------------------------------------------|
| Any number (includes 0)                        | `\d+`                                                | `/posts/{id:\d+}`                                                   |
| **Positive integer (without 0)**               | `[1-9]\d*`                                           | `/users/{id:[1-9]\d*}`                                              |
| Number (includes 0, no leading zeros)           | `[1-9]\d*\|0`                                        | `/items/{id:[1-9]\d*\|0}`                                           |
| Slug (letters, numbers, and dashes)                   | `[a-z0-9-]+`                                         | `/products/{slug:[a-z0-9-]+}`                                       |
| Letters only                                     | `[a-zA-Z]+`                                          | `/category/{name:[a-zA-Z]+}`                                        |
| UUID (standard format)                             | `[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}` | `/orders/{uuid:[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}}` |
| File name with a specific extension (e.g., pdf) | `.+\.pdf`                                            | `/files/{filename:.+\.pdf}`                                         |

---

## Dependency Injection and Responses

### Controllers

The router uses the Dependency Injection container to create controller instances. This means that your controllers can receive other classes (services, repositories, etc.) in their constructor, and they will be resolved automatically.

```php
<?php
namespace App\Module\User\Controller;

use App\Module\User\Service\IUserService;

class UserController
{
    // The UserService will be automatically injected by the DI container.
    public function __construct(protected IUserService $userService) {}

    public function getAll() {
        // ...
    }
}
```

### Returning Responses

All route handlers (whether controller methods or Closures) **must** return an instance of `App\Core\Http\DefaultResponse`. The router is responsible for taking this object and sending the appropriate HTTP response to the client.

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
