<?php

namespace App\Core\Exception;

class AppException extends \Exception {
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function toLog(): string {
        return sprintf(
            "[%s] Code: %d, Message: %s, File: %s, Line: %d",
            date('Y-m-d H:i:s'),
            $this->getCode(),
            $this->getMessage(),
            $this->getFile(),
            $this->getLine()
        );
    }
}