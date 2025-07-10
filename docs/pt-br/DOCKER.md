# Documentação do Ambiente Docker

Este documento detalha a configuração do ambiente Docker utilizado no projeto, incluindo o `Dockerfile`, o `docker-compose.yml` e o script de `entrypoint.sh`.

## Visão Geral

O ambiente Docker foi projetado para fornecer um ambiente de desenvolvimento e produção consistente e isolado. Ele é composto por dois serviços principais:

-   **`app`**: O container da aplicação, que executa o PHP com Apache.
-   **`mysql`**: O container do banco de dados MySQL.

---

## Dockerfile

O `Dockerfile` é responsável por construir a imagem da aplicação. Ele realiza as seguintes etapas:

1.  **Imagem Base**: Utiliza a imagem `php:8.4.8-apache` como base.
2.  **Instalação de Dependências**: Instala as extensões PHP necessárias, como `gd`, `zip`, `mysqli`, `pdo`, `pdo_mysql`, `raphf` e `http`, além de pacotes como `curl`, `git` e `unzip`.
3.  **Composer**: Baixa e instala o Composer globalmente.
4.  **Configuração do Apache**: Copia o arquivo de configuração do Apache (`000-default.conf`) e habilita o `mod_rewrite`.
5.  **Permissões**: Define as permissões corretas para o diretório `/var/www/html`.
6.  **Diretório de Trabalho**: Define o diretório de trabalho como `/var/www/html`.
7.  **Entrypoint**: Copia e torna executáveis os scripts `entrypoint.sh` e `wait-for-it.sh`, e define o `entrypoint.sh` como o script de inicialização do container.

---

## docker-compose.yml

O `docker-compose.yml` orquestra os serviços da aplicação. Ele define os seguintes serviços e configurações:

### Serviço `app`

-   **`build`**: Constrói a imagem da aplicação a partir do `Dockerfile` no contexto do projeto.
-   **`container_name`**: Define o nome do container como `modularphp-api`.
-   **`volumes`**: Mapeia os seguintes diretórios:
    -   `./app:/app`: Mapeia o diretório `app` local para o diretório `/app` no container.
    -   `./public:/var/www/html/public`: Mapeia o diretório `public` local para o diretório `/var/www/html/public` no container.
    -   `./conf/db-conf:/etc/db-conf`: Mapeia o diretório de configuração do banco de dados para o container.
-   **`networks`**: Conecta o container à rede `application-network`.
-   **`ports`**: Mapeia a porta `8080` do host para a porta `80` do container.
-   **`working_dir`**: Define o diretório de trabalho como `/app`.

### Serviço `mysql`

-   **`image`**: Utiliza a imagem `mysql:8.1.0`.
-   **`container_name`**: Define o nome do container como `modularphp-mysql`.
-   **`environment`**: Define as variáveis de ambiente para o banco de dados.
-   **`volumes`**: Mapeia os seguintes diretórios:
    -   `mysql_data:/var/lib/mysql`: Cria um volume nomeado para persistir os dados do banco de dados.
    -   `./conf/db-conf:/etc/db-conf`: Mapeia o diretório de configuração do banco de dados para o container.
-   **`networks`**: Conecta o container à rede `application-network`.
-   **`ports`**: Mapeia a porta `3306` do host para a porta `3306` do container.

### Redes e Volumes

-   **`application-network`**: Uma rede do tipo `bridge` para a comunicação entre os containers.
-   **`mysql_data`**: Um volume nomeado para persistir os dados do banco de dados.

---

## entrypoint.sh

O script `entrypoint.sh` é executado toda vez que o container da aplicação é iniciado. Ele realiza as seguintes tarefas:

1.  **`wait-for-it`**: Aguarda o serviço do MySQL estar disponível antes de continuar.
2.  **Primeira Inicialização**: Verifica se o arquivo `.initialized` existe. Se não existir, significa que é a primeira vez que o container está sendo executado.
    -   **`composer install`**: Se a pasta `vendor` não existir, executa o `composer install`.
    -   **`phinx migrate`**: Executa as migrations do banco de dados.
    -   **`phinx seed:run`**: Executa os seeders do banco de dados.
    -   **Criação do `.initialized`**: Cria o arquivo `.initialized` para que o script não seja executado novamente.
3.  **Execução do Comando Principal**: Executa o comando principal do container (no caso, `apache2-foreground`).
