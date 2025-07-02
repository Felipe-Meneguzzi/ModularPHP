# Generating OpenSSL Keys for the Project

The `app/openssl-keys/` directory is intended for storing the RSA cryptographic keys (`private.key` and `public.key`) necessary for the correct functioning of the application.

## Instructions

To generate the keys, you need to have [OpenSSL](https://www.openssl.org/source/) installed on your system. Follow the steps below in your terminal.
Tip: If you have Git Bash installed, you can run it from there without needing to install anything!

### 1. Generate the Private Key

The first step is to generate the 2048-bit RSA private key. Execute the following command inside the directory:

```bash
openssl genpkey -algorithm RSA -out app/openssl-keys/private.key -pkeyopt rsa_keygen_bits:2048
```

At the end of the execution, a file named `private.key` will be created in this directory.

**Attention:** The private key is secret and should never be shared or versioned in public repositories.

### 2. Generate the Public Key

With the private key in hand, you can generate the corresponding public key. The public key is derived from the private key.

Execute the command below:

```bash
openssl rsa -pubout -in app/openssl-keys/private.key -out app/openssl-keys/public.key
```

This will create the `public.key` file in the same directory.

### 3. Configure the Path in .env

Now with the two keys generated, you must specify the path in your .env with the `SSL_PUBLIC_KEY_PATH` and `SSL_PRIVATE_KEY_PATH` keys.
If nothing has been changed in the Dockerfile and docker-compose, the correct path is already set in the .env.example

***Remember:*** The path is always relative to the container, not your computer.

## Summary

After following the steps, you will have the following files in this directory:

- `private.key`: Your private key. **Keep it safe.**
- `public.key`: Your public key, which can be shared.

Make sure these files are in the correct location so that the application can use them.
