<?php
declare(strict_types = 1);

namespace App\ValueObject;

use App\Core\Exception\AppException;
use Stringable;

final readonly class CNPJ implements Stringable {

    private string $value;

    private function __construct(string $cnpj) {
        $normalizedCNPJ = self::normalize($cnpj);
        self::validate($normalizedCNPJ);

        $this->value = $normalizedCNPJ;
    }

    public static function fromString(string $cnpj): self {
        return new self($cnpj);
    }

    private static function validate(string $cnpj): void {
        if (!self::isValidCNPJ($cnpj)) {
            throw new AppException("Invalid CNPJ given: '{$cnpj}'", 400);
        }
    }

    private static function normalize(string $cnpj): string {
        return preg_replace('/\D/', '', $cnpj); 
    }

    private static function isValidCNPJ(string $cnpj): bool {
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        if ($cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        return $cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }

    public function __toString(): string {
        return $this->value;
    }

    public function equals(CNPJ $other): bool {
        return $this->value === $other->value;
    }

    /**
     * Returns the formatted CNPJ in the default format (##.###.###/####-##).
     */
    public function getFormatted(): string {
        return vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($this->value));
    }
}
