<?php

namespace App\Core\Http;

use App\Core\ObjectCore;
use DateTime;
use DateTimeZone;

class HTTPRequest {
    public string $uri;
    public array $path;
    public string $method;
    public array $headers;
    public array $queryParams;
    public array $body;
    public array $cookies;
    public string $userAgent;
    public string $referer;
    public array $requestTime;
    public string $requestIP;
    public array $dynamicParams;
    public array $middlewareParams;

    public function __construct(){
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $this->queryParams = $_GET;
        $this->path = explode('/', trim($this->queryParams['path'] ?? '', '/'));
        $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $this->referer = $_SERVER['HTTP_REFERER'] ?? 'Unknown';
        $this->requestTime = self::formatRequestTime();
        $this->requestIP = self::getClientIP();
        $this->dynamicParams = [];
        $this->middlewareParams = [];
    }

    private function getClientIP(): string {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipList[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function formatRequestTime(): array {
        try {
            $requestTime = $_SERVER['REQUEST_TIME'] ?? time();
            $dateTime = new DateTime("@$requestTime");
            $dateTime->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            return [
                'data' => $dateTime->format('d/m/Y'),
                'hora' => $dateTime->format('H:i:s'),
            ];
        } catch (\Exception $e) {
            return [
                'data' => 'Unknown',
                'hora' => 'Unknown'
            ];
        }
    }
}
