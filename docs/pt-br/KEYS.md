# Geração de Chaves OpenSSL para o Projeto

O diretório `app/openssl-keys/` é destinado ao armazenamento das chaves criptográficas RSA (`private.key` e `public.key`) necessárias para o correto funcionamento da aplicação.

## Instruções

Para gerar as chaves, você precisa ter o [OpenSSL](https://www.openssl.org/source/) instalado em seu sistema. Siga os passos abaixo no seu terminal.
Dica: Se você tem o Git Bash instalado, pode executar por ele sem necessidade de instalar!

### 1. Gerar a Chave Privada

O primeiro passo é gerar a chave privada RSA de 2048 bits. Execute o seguinte comando dentro do diretório:

```bash
openssl genpkey -algorithm RSA -out app/openssl-keys/private.key -pkeyopt rsa_keygen_bits:2048
```

Ao final da execução, um arquivo chamado `private.key` será criado neste diretório.

**Atenção:** A chave privada é secreta e nunca deve ser compartilhada ou versionada em repositórios públicos.

### 2. Gerar a Chave Pública

Com a chave privada em mãos, você pode gerar a chave pública correspondente. A chave pública é derivada da chave privada.

Execute o comando abaixo:

```bash
openssl rsa -pubout -in app/openssl-keys/private.key -out app/openssl-keys/public.key
```

Isso criará o arquivo `public.key` no mesmo diretório.

### 3. Configurar o Caminho no .env

Agora com as duas chaves geradas, você deve especificar o caminho no seu .env com as chaves `SSL_PUBLIC_KEY_PATH` e `SSL_PRIVATE_KEY_PATH`
Caso não tenha sido alterado nada no Dockerfile e docker-compose o caminho correto ja está setado no .env.example

***Lembre-se:*** O caminho sempre é relativo ao container, e não ao seu computador.

## Resumo

Após seguir os passos, você terá os seguintes arquivos neste diretório:

- `private.key`: Sua chave privada. **Mantenha-a segura.**
- `public.key`: Sua chave pública, que pode ser compartilhada.

Certifique-se de que esses arquivos estejam no local correto para que a aplicação possa utilizá-los.