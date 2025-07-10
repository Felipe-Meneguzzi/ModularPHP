# Autenticação com JWT (JSON Web Token)

Este documento detalha o sistema de autenticação da API, que é baseado em JSON Web Tokens (JWT) com assinatura assimétrica RSA (RS256). Essa abordagem garante uma comunicação segura e stateless entre o cliente e o servidor.

## Visão Geral

A autenticação JWT funciona da seguinte forma:

1.  **Login**: O cliente envia suas credenciais (usuário e senha) para um endpoint de login.
2.  **Geração do Token**: Se as credenciais forem válidas, o servidor gera um JWT assinado com uma chave privada.
3.  **Armazenamento**: O cliente armazena o token de forma segura (por exemplo, em `localStorage` ou `sessionStorage`).
4.  **Requisições Autenticadas**: Para acessar rotas protegidas, o cliente envia o JWT no cabeçalho `Authorization` de cada requisição, no formato `Bearer {token}`.
5.  **Validação**: O servidor utiliza uma chave pública para verificar a assinatura do token. Se for válido, a requisição é processada.

---

## Como Usar

### 1. Obtendo o Token

Para obter um token, o cliente deve fazer uma requisição `POST` para o endpoint `/login` com as credenciais do usuário no corpo da requisição:

```json
{
  "login": "admin",
  "password": "admin"
}
```

Se as credenciais estiverem corretas, a API retornará um token JWT:

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### 2. Enviando o Token nas Requisições

Para acessar rotas protegidas, o cliente deve incluir o token no cabeçalho `Authorization`:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

O `AuthenticateMiddleware` irá interceptar a requisição, validar o token e, se for válido, permitir o acesso ao recurso.

---

## Estrutura do Payload do Token

O payload do JWT contém informações (claims) sobre o usuário e o próprio token. A estrutura do payload é a seguinte:

```json
{
  "user": {
    "uuid": "c255f364-50f7-1110-92f8-4af298741893",
    "name": "Administrador",
    "login": "admin",
    "email": "admin@gmail.com",
    "phone": null,
    "user_type": "c255f364-50f7-11f0-9nf8-4af298741892",
    "cpf": "04739633027",
    "building": "89374cd6-a32d-4579-8e89-def02da000e5",
    "company": "89374cd6-a32d-4579-8e89-def02da094e4"
  },
  "original_request": {
    "ip": "172.18.0.1",
    "user_agent": "insomnia/11.0.1"
  },
  "iat": 1751396729,
  "iss": "modularphp.com",
  "nbf": 1751396729,
  "exp": 1751425529
}
```

### Descrição dos Campos

-   **`user`**: Contém os dados do usuário autenticado.
    -   **`uuid`**: Identificador único do usuário.
    -   **`name`**: Nome do usuário.
    -   **`login`**: Login do usuário.
    -   **`email`**: E-mail do usuário.
    -   **`phone`**: Telefone do usuário.
    -   **`user_type`**: Tipo de usuário.
    -   **`cpf`**: CPF do usuário.
    -   **`building`**: Edifício do usuário.
    -   **`company`**: Empresa do usuário.
-   **`original_request`**: Contém informações sobre a requisição original que gerou o token.
    -   **`ip`**: Endereço IP do cliente.
    -   **`user_agent`**: User agent do cliente.
-   **`iat` (Issued At)**: Timestamp de quando o token foi gerado.
-   **`iss` (Issuer)**: Emissor do token (a aplicação).
-   **`nbf` (Not Before)**: Timestamp de quando o token se torna válido.
-   **`exp` (Expiration Time)**: Timestamp de quando o token expira.