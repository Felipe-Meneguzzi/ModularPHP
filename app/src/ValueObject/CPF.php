<?php
declare(strict_types = 1);

namespace App\ValueObject;

use App\Core\Exception\AppException;
use Stringable;

final readonly class CPF implements Stringable {

    private string $value;

    private function __construct(string $cpf) {
        $normalizedCPF = self::normalize($cpf);
        self::validate($normalizedCPF);

        $this->value = $normalizedCPF;
    }

    public static function fromString(string $cpf): self {
        return new self($cpf);
    }

    private static function validate(string $cpf): void {
        if (!self::isValidCPF($cpf)) {
            throw new AppException("Invalid CPF given: '{$cpf}'", 400);
        }
    }

    private static function normalize(string $cpf): string {
        return preg_replace('/\D/', '', $cpf); 
    }

    private static function isValidCPF(string $cpf): bool {
        // CPF must have exactly 11 digits.
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validation of verifier digits.
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string {
        return $this->value;
    }

    public function equals(CPF $other): bool {
        return $this->value === $other->value;
    }

    /**
     * Returns the formatted CPF in the default format (###.###.###-##).
     */
    public function getFormatted(): string {
        return vsprintf('%s.%s.%s-%s', str_split($this->value));
    }
}
