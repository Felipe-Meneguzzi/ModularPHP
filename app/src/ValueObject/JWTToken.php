<?php
declare(strict_types=1);

namespace App\ValueObject;

use App\Core\Exception\AppException;
use DateTimeImmutable;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Stringable;

final readonly class JWTToken implements Stringable {

    private string $value;

    private function __construct(array $payload) {
        try {
            $privateKey = file_get_contents($_ENV['SSL_PRIVATE_KEY_PATH']);
        }catch (\Throwable $throwable){
            throw new AppException('Unable to read private key, please check /docs/KEYS.md to configure your key.');
        }

        $this->value = JWT::encode($payload, $privateKey, $_ENV['JWT_ALGORITHM']);
    }

    public static function fromPayload(array $payload, int $activationHours = 0): self {
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+' . $_ENV['JWT_EXPIRE_HOURS'] . ' hour')->getTimestamp();
        $serverName = $_ENV['APP_DOMAIN'];
        $notBefore  = $issuedAt->modify('+' . $activationHours . ' hour')->getTimestamp();

        $payload['iat'] = $issuedAt->getTimestamp();
        $payload['iss'] = $serverName;
        $payload['nbf'] = $notBefore;
        $payload['exp'] = $expire;

        return new self($payload);
    }

    public static function decode(string $token) {
        try {
            $publicKey = file_get_contents($_ENV['SSL_PUBLIC_KEY_PATH']);
        }catch (\Throwable $throwable){
            throw new AppException('Unable to read public key, please check /docs/KEYS.md to configure your key.');
        }

        return JWT::decode($token, new Key($publicKey, $_ENV['JWT_ALGORITHM']));
    }

    public function __toString(): string {
        return $this->value;
    }

}