# Authentication with JWT (JSON Web Token)

This document details the API's authentication system, which is based on JSON Web Tokens (JWT) with an RSA asymmetric signature (RS256). This approach ensures secure and stateless communication between the client and the server.

## Overview

JWT authentication works as follows:

1.  **Login**: The client sends its credentials (username and password) to a login endpoint.
2.  **Token Generation**: If the credentials are valid, the server generates a JWT signed with a private key.
3.  **Storage**: The client stores the token securely (e.g., in `localStorage` or `sessionStorage`).
4.  **Authenticated Requests**: To access protected routes, the client sends the JWT in the `Authorization` header of each request, in the format `Bearer {token}`.
5.  **Validation**: The server uses a public key to verify the token's signature. If it is valid, the request is processed.

---

## How to Use

### 1. Obtaining the Token

To obtain a token, the client must make a `POST` request to the `/login` endpoint with the user's credentials in the request body:

```json
{
  "login": "admin",
  "password": "admin"
}
```

If the credentials are correct, the API will return a JWT:

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### 2. Sending the Token in Requests

To access protected routes, the client must include the token in the `Authorization` header:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

The `AuthenticateMiddleware` will intercept the request, validate the token, and if valid, allow access to the resource.

---

## Token Payload Structure

The JWT payload contains information (claims) about the user and the token itself. The payload structure is as follows:

```json
{
  "user": {
    "uuid": "c255f364-50f7-1110-92f8-4af298741893",
    "name": "Administrator",
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
  "iss": "myapp.com.br",
  "nbf": 1751396729,
  "exp": 1751425529
}
```

### Field Descriptions

-   **`user`**: Contains the authenticated user's data.
    -   **`uuid`**: Unique user identifier.
    -   **`name`**: User's name.
    -   **`login`**: User's login.
    -   **`email`**: User's email.
    -   **`phone`**: User's phone number.
    -   **`user_type`**: User type.
    -   **`cpf`**: User's CPF.
    -   **`building`**: User's building.
    -   **`company`**: User's company.
-   **`original_request`**: Contains information about the original request that generated the token.
    -   **`ip`**: Client's IP address.
    -   **`user_agent`**: Client's user agent.
-   **`iat` (Issued At)**: Timestamp of when the token was generated.
-   **`iss` (Issuer)**: Issuer of the token (the application).
-   **`nbf` (Not Before)**: Timestamp of when the token becomes valid.
-   **`exp` (Expiration Time)**: Timestamp of when the token expires.
