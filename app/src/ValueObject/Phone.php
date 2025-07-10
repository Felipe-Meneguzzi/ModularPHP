<?php
declare(strict_types = 1);

namespace App\ValueObject;

use App\Core\Exception\AppException;
use Stringable;

final readonly class Phone implements Stringable {
    private string $value;

    private function __construct(string $phone) {
        $this->value = $this->sanitizeAndValidate($phone);
    }

    public static function fromString(string $phone): self {
        return new self($phone);
    }

    private function sanitizeAndValidate(string $phone): string {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleanPhone) !== 13) {
            throw new AppException(
                "Phone must have exactly 13 digits. Received: {$cleanPhone} (" . strlen($cleanPhone) . " digits)",
                400
            );
        }
        
        return $cleanPhone;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function getFormatted(): string {
        // Formato: +55 11 99781-4962
        return sprintf(
            '+%s (%s) %s-%s',
            substr($this->value, 0, 2),  // +55
            substr($this->value, 2, 2),  // 11
            substr($this->value, 4, 5),  // 99781
            substr($this->value, 9, 4)   // 4962
        );
    }

    public function getCountryCode(): string {
        return substr($this->value, 0, 2);
    }

    public function getAreaCode(): string {
        return substr($this->value, 2, 2);
    }

    public function getLocalvalue(): string {
        return substr($this->value, 4);
    }

    public function equals(Phone $other): bool {
        return $this->value === $other->value;
    }

    public function __toString(): string {
        return $this->value;
    }

    public function toArray(): array {
        return [
            'raw' => $this->value,
            'formatted' => $this->getFormatted(),
            'country_code' => $this->getCountryCode(),
            'area_code' => $this->getAreaCode(),
            'local_value' => $this->getLocalvalue()
        ];
    }

}