# Docker Environment Documentation

This document details the configuration of the Docker environment used in the project, including the `Dockerfile`, `docker-compose.yml`, and the `entrypoint.sh` script.

## Overview

The Docker environment is designed to provide a consistent and isolated development and production environment. It is composed of two main services:

-   **`app`**: The application container, which runs PHP with Apache.
-   **`mysql`**: The MySQL database container.

---

## Dockerfile

The `Dockerfile` is responsible for building the application image. It performs the following steps:

1.  **Base Image**: Uses the `php:8.4.8-apache` image as a base.
2.  **Dependency Installation**: Installs the necessary PHP extensions, such as `gd`, `zip`, `mysqli`, `pdo`, `pdo_mysql`, `raphf`, and `http`, as well as packages like `curl`, `git`, and `unzip`.
3.  **Composer**: Downloads and installs Composer globally.
4.  **Apache Configuration**: Copies the Apache configuration file (`000-default.conf`) and enables `mod_rewrite`.
5.  **Permissions**: Sets the correct permissions for the `/var/www/html` directory.
6.  **Working Directory**: Sets the working directory to `/var/www/html`.
7.  **Entrypoint**: Copies and makes executable the `entrypoint.sh` and `wait-for-it.sh` scripts, and sets `entrypoint.sh` as the container's startup script.

---

## docker-compose.yml

The `docker-compose.yml` file orchestrates the application's services. It defines the following services and configurations:

### `app` Service

-   **`build`**: Builds the application image from the `Dockerfile` in the project's context.
-   **`container_name`**: Sets the container name to `modularphp-api`.
-   **`volumes`**: Maps the following directories:
    -   `./app:/app`: Maps the local `app` directory to the `/app` directory in the container.
    -   `./public:/var/www/html/public`: Maps the local `public` directory to the `/var/www/html/public` directory in the container.
    -   `./conf/db-conf:/etc/db-conf`: Maps the database configuration directory to the container.
-   **`networks`**: Connects the container to the `application-network` network.
-   **`ports`**: Maps port `8080` of the host to port `80` of the container.
-   **`working_dir`**: Sets the working directory to `/app`.

### `mysql` Service

-   **`image`**: Uses the `mysql:8.1.0` image.
-   **`container_name`**: Sets the container name to `modularphp-mysql`.
-   **`environment`**: Sets the environment variables for the database.
-   **`volumes`**: Maps the following directories:
    -   `mysql_data:/var/lib/mysql`: Creates a named volume to persist the database data.
    -   `./conf/db-conf:/etc/db-conf`: Maps the database configuration directory to the container.
-   **`networks`**: Connects the container to the `application-network` network.
-   **`ports`**: Maps port `3306` of the host to port `3306` of the container.

### Networks and Volumes

-   **`application-network`**: A `bridge` type network for communication between the containers.
-   **`mysql_data`**: A named volume to persist the database data.

---

## entrypoint.sh

The `entrypoint.sh` script is executed every time the application container is started. It performs the following tasks:

1.  **`wait-for-it`**: Waits for the MySQL service to be available before continuing.
2.  **First Initialization**: Checks if the `.initialized` file exists. If it doesn't, it means it's the first time the container is being run.
    -   **`composer install`**: If the `vendor` folder doesn't exist, it runs `composer install`.
    -   **`phinx migrate`**: Runs the database migrations.
    -   **`phinx seed:run`**: Runs the database seeders.
    -   **Creation of `.initialized`**: Creates the `.initialized` file so that the script is not executed again.
3.  **Execution of the Main Command**: Executes the container's main command (in this case, `apache2-foreground`).
