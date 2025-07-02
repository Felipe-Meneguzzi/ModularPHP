#!/bin/bash
set -e

INIT_FILE="/usr/local/bin/.initialized"

/usr/local/bin/wait-for-it myapp-mysql:3306 --timeout=30 -- echo "MySQL está disponível!"

if [ ! -f "$INIT_FILE" ]; then
  echo "Primeira inicialização detectada..."
  touch "$INIT_FILE"

  if [ ! -d "/app/vendor" ]; then
    echo "Pasta vendor não encontrada. Executando composer install..."
    composer install --no-dev --optimize-autoloader
    composer dump-autoload
  fi

  echo "Executando migrações..."
  /app/vendor/bin/phinx migrate --configuration /app/phinx.php

  echo "Executando seeds..."
  /app/vendor/bin/phinx seed:run --configuration /app/phinx.php
else
  echo "Inicialização já realizada anteriormente. Pulando setup inicial."
fi

exec "$@"
