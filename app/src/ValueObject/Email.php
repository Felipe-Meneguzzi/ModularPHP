<?php
declare(strict_types=1);

namespace App\ValueObject;

use App\Core\Exception\AppException;
use Stringable;

final readonly class Email implements Stringable {

    private string $value;

    private function __construct(string $emailAddress) {
        $normalizedEmail = self::normalize($emailAddress);
        self::validate($normalizedEmail);

        $this->value = $normalizedEmail;
    }

    public static function fromString(string $emailAddress): self {
        return new self($emailAddress);
    }

    private static function validate(string $emailAddress): void {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new AppException("Formato de e-mail inválido fornecido: '{$emailAddress}'", 400);
        }
    }

    private static function normalize(string $emailAddress): string {
        return strtolower(trim($emailAddress));
    }

    public function __toString(): string {
        return $this->value;
    }

    public function equals(Email $other): bool {
        return $this->value === $other->value;
    }

    /**
     * Retorna a parte local (antes do @) do e-mail.
     */
    public function getLocalPart(): string {
        return explode('@', $this->value, 2)[0];
    }

    /**
     * Retorna o domínio (depois do @) do e-mail.
     */
    public function getDomain(): string {
        return explode('@', $this->value, 2)[1];
    }
}