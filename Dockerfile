FROM php:8.4.8-apache

# Instalação de extensões comuns, dependências da ICU e do Composer
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mysqli pdo pdo_mysql \
    && pecl install raphf \
    && docker-php-ext-enable raphf \
    && pecl install pecl_http \
    && docker-php-ext-enable http \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia o arquivo de configuração do Apache
COPY conf/apache-conf/000-default.conf /etc/apache2/sites-available/000-default.conf

# Habilitar módulos do Apache
RUN a2enmod rewrite

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copia o composer.json para rodar o install
COPY app/composer.json /app/composer.json

# Rodar o script de inicialização, roda => composer dump-autoload, composer install, e criação do arquivo do swagger automaticamente ao construir a imagem
COPY conf/container-conf/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
COPY conf/container-conf/wait-for-it.sh /usr/local/bin/wait-for-it
RUN chmod +x /usr/local/bin/wait-for-it
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

# Expor a porta 80 para o Apache
EXPOSE 80
