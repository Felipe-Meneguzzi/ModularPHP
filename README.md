# ModularPHP

This repository serves as a robust and scalable template for building new APIs in PHP. It is designed with a modular architecture and good development practices to ensure that your project starts with a solid, secure, and easily maintainable foundation.

The goal of this template is to provide a complete and ready-to-use structure, allowing development teams to focus on business logic rather than worrying about the initial environment setup and project architecture.

## Key Features

-   **🚀 Dockerized Environment**: Complete setup with Docker and Docker Compose for PHP/Apache and MySQL services, ensuring a consistent development and production environment.
-   **🔭 Observability Stack (Logs, Metrics, and Traces)**: Native integration with **OpenTelemetry** to collect and send data to a set of cutting-edge monitoring tools, including:
    -   **Prometheus**: To collect and store metrics.
    -   **Grafana Tempo**: To store and query distributed traces.
    -   **Grafana Loki**: To aggregate and query logs.
    -   **Grafana**: To visualize all data in interactive dashboards.
-   **🧱 Modular Architecture**: The code is organized into independent business modules, facilitating maintenance, decoupling, and system scalability.
-   **🔒 Secure Authentication with JWT**: Authentication system based on JSON Web Tokens with RSA asymmetric signature (RS256), ensuring secure and stateless communication.
-   **🗃️ Migrations and Seeders with Phinx**: Database management with Phinx, allowing the creation of migrations and seeders for schema versioning and initial data population.
-   **⚙️ Dependency Injection**: Uses a Dependency Injection container (PHP-DI) to manage instances and promote low coupling between components.
-   **🛣️ Advanced Routing System**: A flexible router that supports RESTful verbs, route grouping, pipeline middlewares, and parameter validation with Regex.
-   **✨ Data Validation with `ValueObject`**: An input data validation layer that ensures data integrity before it reaches the business logic.

---

## Getting Started

Follow the steps below to set up and run the project locally.

### Prerequisites

-   **Docker and Docker Compose**: Make sure both are installed and running on your machine.
-   **OpenSSL**: Required to generate the security keys. (Tip: If you use Git for Windows, Git Bash already includes OpenSSL).

### Installation Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/felipe-meneguzzi/modularphp.git
    cd ModularPHP
    ```

2.  **Generate the Security Keys (Required):**
    The RSA keys are essential for signing the JWTs. They must be created in the `app/openssl-keys/` directory.

    ```bash
    # 1. Generate the private key
    openssl genpkey -algorithm RSA -out app/openssl-keys/private.key -pkeyopt rsa_keygen_bits:2048

    # 2. Generate the public key from the private one
    openssl rsa -pubout -in app/openssl-keys/private.key -out app/openssl-keys/public.key
    ```
    > **Attention:** The private key (`private.key`) is already ignored in `.gitignore`. Never send it to public repositories.

3.  **Configure Environment Variables:**
    Inside the `app/` folder, create your `.env` file (you can copy and rename `.env.example`) and adjust the database, JWT, and other variables as needed.

4.  **Start the Docker Containers:**
    Run the following command in the project root. It will build the images and start the services.
    ```bash
    docker-compose up -d --build
    ```
    -   **API**: `http://localhost:8080`
    -   **MySQL**: `localhost:3306`
    -   **Grafana**: `http://localhost:3000` (user: `admin`, pass: `admin`)
    -   **Prometheus**: `http://localhost:9190`

5.  **Run Migrations and Seeders:**
    The container's entry script already runs `composer install`, migrations, and seeders. If you need to run them manually, use the commands below:

    ```bash
    # To run migrations
    docker compose exec app vendor/bin/phinx migrate

    # To run seeders
    docker compose exec app vendor/bin/phinx seed:run
    ```

---

## Project Documentation

The `/docs` folder contains detailed documentation on specific components. Before you start developing, it is highly recommended to read:

-   **`ROUTER.md`**: Explains how the routing system works, how to create routes, group them, use dynamic parameters, and apply middlewares.
-   **`KEYS.md`**: Describes in detail the process for generating the OpenSSL keys (public and private).

---

## Architectures and Patterns

This template adopts a combination of patterns to ensure clean, decoupled, and testable code.

### 1. Modular Architecture

The application is divided into **Modules**, located in `app/src/Module/`. Each module represents a business domain (e.g., `Login`, `User`). This approach promotes:
* **Low Coupling**: Modules are independent and should not know each other directly.
* **High Cohesion**: Logic related to the same domain remains together.
* **Scalability**: New modules can be added with minimal impact on existing code.

### 2. Service-Repository Pattern

* **Services (Service Layer)**: Contain the business logic and application rules (e.g., `UserLoginService`). They orchestrate operations and interact with repositories.
* **Repositories (Repository Layer)**: Are responsible for communication with the data source (e.g., `UserLoginRepository`). They abstract the data access logic (SQL, etc.).

### 3. Dependency Injection (DI)

We use the **PHP-DI** container to manage dependencies, configured in `app/src/Core/AppDIContainer.php`. Instead of instantiating classes manually (`new MyClass()`), you request them in the constructor, and the container resolves them automatically.

### 4. ValueObjects

* **ValueObjects**: Objects that carry data between layers (e.g., `CPF`, `Email`). They ensure that input data is explicit and typed.


---

## How to Create a New Module

To maintain the project's architectural pattern, follow these steps when creating a new module (e.g., "Product"):

1.  **Folder Structure**: In `app/src/Module/`, create the `Product/` folder with the subfolders `Controller`, `Service`, and `Repository`.

2.  **Migration**: In `conf/db-conf/migrations/`, create a new migration file with Phinx.

3.  **Entity**: In `app/src/Entity/`, create the `ProductEntity.php` class.

4.  **Repository**:
    * In `.../Product/Repository/`, create the `ProductRepository.php` class.

5.  **Service**:
    * In `.../Product/Service/`, create the `ProductService.php` class that injects `ProductRepository` in its constructor.

6.  **Controller**: In `.../Product/Controller/`, create `ProductController.php`, injecting `ProductService`.

7.  **Dependency Injection**: Register the new classes in the `AppDIContainer.php` container.
    ```php
    // In AppDIContainer.php
    $builder->addDefinitions([
        // ...
        IProductService::class => autowire(ProductService::class),
        IProductRepository::class => autowire(ProductRepository::class),
    ]);
    ```

8.  **Routes**: Add the new module's routes in `app/src/Api.php`, pointing to the `ProductController` methods.
    ```php
    // In Api.php
    $router->group(['prefix' => '/product', 'middleware' => [AuthenticateMiddleware::class]], function ($router) {
        $router->get('', [ProductController::class, 'getAll']);
        $router->post('', [ProductController::class, 'create']);
    });
    ```
