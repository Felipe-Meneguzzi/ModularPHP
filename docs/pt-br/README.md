# ModularPHP

Este repositório é um Template PHP para APIs modulares e altamente escaláveis, focado em técnicas de arquitetura limpa e legibilidade. Ele foi projetado com uma arquitetura modular e boas práticas de desenvolvimento para garantir que seu projeto comece com uma base sólida, segura e de fácil manutenção.

O objetivo deste template é fornecer uma estrutura completa e pronta para uso, permitindo que as equipes de desenvolvimento foquem na lógica de negócio em vez de se preocuparem com a configuração inicial do ambiente e a arquitetura do projeto.

## Principais Funcionalidades

-   **🚀 Ambiente Dockerizado**: Configuração completa com Docker e Docker Compose para os serviços de PHP/Apache e MySQL, garantindo um ambiente de desenvolvimento e produção consistente.
-   **🔭 Stack de Observabilidade (Logs, Métricas e Traces)**: Integração nativa com **OpenTelemetry** para coletar e enviar dados para um conjunto de ferramentas de monitoramento de ponta, incluindo:
    -   **Prometheus**: Para coletar e armazenar métricas.
    -   **Grafana Tempo**: Para armazenar e consultar traces distribuídos.
    -   **Grafana Loki**: Para agregar e consultar logs.
    -   **Grafana**: Para visualizar todos os dados em dashboards interativos.
-   **🧱 Arquitetura Modular**: O código é organizado em módulos de negócio independentes, facilitando a manutenção, o desacoplamento e a escalabilidade do sistema.
-   **🔒 Autenticação Segura com JWT**: Sistema de autenticação baseado em JSON Web Tokens com assinatura assimétrica RSA (RS256), garantindo uma comunicação segura e stateless.
-   **🗃️ Migrations e Seeders com Phinx**: Gerenciamento do banco de dados com Phinx, permitindo a criação de migrations e seeders para versionamento de schema e povoamento de dados iniciais.
-   **⚙️ Injeção de Dependência**: Utiliza um contêiner de Injeção de Dependência (PHP-DI) para gerenciar as instâncias e promover baixo acoplamento entre os componentes.
-   **🛣️ Sistema de Roteamento Avançado**: Um roteador flexível que suporta verbos RESTful, agrupamento de rotas, middlewares em pipeline e validação de parâmetros com Regex.
-   **✨ Validação de Dados com `ValueObject`**: Uma camada de validação de dados de entrada que garante a integridade dos dados antes de chegarem à lógica de negócio.

---

## Começando

Siga os passos abaixo para configurar e executar o projeto localmente.

### Pré-requisitos

-   **Docker e Docker Compose**: Certifique-se de que ambos estão instalados e em execução na sua máquina.
-   **OpenSSL**: Necessário para gerar as chaves de segurança. (Dica: Se você usa Git for Windows, o Git Bash já inclui o OpenSSL).

### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/felipe-meneguzzi/modularphp.git
    cd ModularPHP
    ```

2.  **Gere as Chaves de Segurança (Obrigatório):**
    As chaves RSA são essenciais para a assinatura dos tokens JWT. Elas devem ser criadas no diretório `app/openssl-keys/`.

    ```bash
    # 1. Gere a chave privada
    openssl genpkey -algorithm RSA -out app/openssl-keys/private.key -pkeyopt rsa_keygen_bits:2048

    # 2. Gere a chave pública a partir da privada
    openssl rsa -pubout -in app/openssl-keys/private.key -out app/openssl-keys/public.key
    ```
    > **Atenção:** A chave privada (`private.key`) já está ignorada no `.gitignore`. Nunca a envie para repositórios públicos.

3.  **Configure as Variáveis de Ambiente:**
    Dentro da pasta `app/`, crie o seu arquivo `.env` (você pode copiar e renomear o `.env.example`) e ajuste as configurações do banco de dados, JWT e outras variáveis conforme necessário.

4.  **Suba os Containers Docker:**
    Execute o comando a seguir na raiz do projeto. Ele irá construir as imagens e iniciar os serviços.
    ```bash
    docker-compose up -d --build
    ```
    -   **API**: `http://localhost:8080`
    -   **MySQL**: `localhost:3306`
    -   **Grafana**: `http://localhost:3000` (user: `admin`, pass: `admin`)
    -   **Prometheus**: `http://localhost:9190`

5.  **Execute as Migrations e Seeders:**
    O script de entrada do container já executa o `composer install`, as migrations e os seeders. Se precisar executar manualmente, use os comandos abaixo:

    ```bash
    # Para executar as migrations
    docker compose exec app vendor/bin/phinx migrate

    # Para executar os seeders
    docker compose exec app vendor/bin/phinx seed:run
    ```

---

## Documentação do Projeto

A pasta `/docs` contém documentação detalhada sobre componentes específicos. Antes de começar a desenvolver, é altamente recomendado a leitura:

-   **`ROUTER.md`**: Explica o funcionamento do sistema de roteamento, como criar rotas, agrupar, usar parâmetros dinâmicos e aplicar middlewares.
-   **`KEYS.md`**: Descreve em detalhes o processo para gerar as chaves OpenSSL (pública e privada).

---

## Arquiteturas e Padrões

Este template adota uma combinação de padrões para garantir um código limpo, desacoplado e testável.

### 1. Arquitetura Modular

A aplicação é dividida em **Módulos**, localizados em `app/src/Module/`. Cada módulo representa um domínio de negócio (ex: `Login`, `User`). Essa abordagem promove:
* **Baixo Acoplamento**: Módulos são independentes e não devem se conhecer diretamente.
* **Alta Coesão**: A lógica relacionada a um mesmo domínio permanece junta.
* **Escalabilidade**: Novos módulos podem ser adicionados com mínimo impacto no código existente.

### 2. Padrão Service-Repository

* **Services (Camada de Serviço)**: Contêm a lógica de negócio e as regras da aplicação (ex: `UserLoginService`). Eles orquestram as operações e interagem com os repositórios.
* **Repositories (Camada de Repositório)**: São responsáveis pela comunicação com a fonte de dados (ex: `UserLoginRepository`). Eles abstraem a lógica de acesso a dados (SQL, etc.).

### 3. Injeção de Dependência (DI)

Utilizamos o container **PHP-DI** para gerenciar as dependências, configurado em `app/src/Core/AppDIContainer.php`. Em vez de instanciar classes manualmente (`new MinhaClasse()`), você as solicita no construtor, e o container as resolve automaticamente.

### 4. ValueObjects

* **ValueObjects**: Objetos que carregam dados entre as camadas (ex: `CPF`, `Email`). Eles garantem que os dados de entrada sejam explícitos e tipados.


---

## Como Criar um Módulo Novo

Para manter o padrão arquitetural do projeto, siga estes passos ao criar um novo módulo (ex: "Product"):

1.  **Estrutura de Pastas**: Em `app/src/Module/`, crie a pasta `Product/` com as subpastas `Controller`, `Service`, e `Repository`.

2.  **Migration**: Em `conf/db-conf/migrations/`, crie um novo arquivo de migration com o Phinx.

3.  **Entity**: Em `app/src/Entity/`, crie a classe `ProductEntity.php`.

4.  **Repository**:
    * Em `.../Product/Repository/`, crie a classe `ProductRepository.php`.

5.  **Service**:
    * Em `.../Product/Service/`, crie a classe `ProductService.php` que injeta `ProductRepository` em seu construtor.

6.  **Controller**: Em `.../Product/Controller/`, crie `ProductController.php`, injetando `ProductService`.

7.  **Injeção de Dependência**: Registre as novas classes no container `AppDIContainer.php`.
    ```php
    // Em AppDIContainer.php
    $builder->addDefinitions([
        // ...
        IProductService::class => autowire(ProductService::class),
        IProductRepository::class => autowire(ProductRepository::class),
    ]);
    ```

8.  **Rotas**: Adicione as rotas do novo módulo em `app/src/Api.php`, apontando para os métodos do `ProductController`.
    ```php
    // Em Api.php
    $router->group(['prefix' => '/product', 'middleware' => [AuthenticateMiddleware::class]], function ($router) {
        $router->get('', [ProductController::class, 'getAll']);
        $router->post('', [ProductController::class, 'create']);
    });
    ```