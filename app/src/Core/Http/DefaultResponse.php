<?php

namespace App\Core\Http;

use App\Core\ObjectCore;
use Illuminate\Database\Eloquent\Model;

class DefaultResponse {
    public array $headers;
    public array $body;

    public function __construct(
        int $statusCode,
        array|Model $data = [],
        array $metadata = [],
        string $message = '',
        array $errors = [],
        array $headers = ['Content-Type' => 'application/json; charset=UTF-8'],
    ) {
        $this->body = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'statusCode' => $statusCode,
            'data' => $data,
            'metadata' => $metadata,
            'message' => $message,
            'errors' => $errors,
        ];
        $this->headers = $headers;
    }

    public function sendResponse (): void {
        http_response_code($this->body['statusCode']);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($this->body);
    }

    public static function getDefaultResponse(array $serviceResponse): self {
        $response = new self(
            statusCode: $serviceResponse['statusCode'] ?? 200,
            data: $serviceResponse['data'] ?? [],
            metadata:  $serviceResponse['metadata'] ?? [],
            message: $serviceResponse['message'] ?? '',
            errors: $serviceResponse['errors'] ?? [],
        );

        if (isset($serviceResponse['headers'])) {
            $response->headers = $serviceResponse['headers'];
        }

        return $response;
    }

    public function getStatusCode() {
        return $this->body['statusCode'];
    }

    public function getStatus() {
        return $this->body['success'];
    }
}