<?php

namespace App\Core\Exception;

class PaginationStackException extends AppException {
    private array $errors;


    public function __construct(array $errors, int $code = 500, ?\Throwable $previous = null) {
        $this->errors = $errors;

        $message = '';
        $num = 1;

        foreach ($errors as $error) {
            $message =  $message . $num . ':' . $error . ' ';
            $num++;
        }

        parent::__construct($message, $code, $previous);
    }


    public function getErrors(): array {
        return $this->errors;
    }
}