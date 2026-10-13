<?php

namespace Core;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private ?array $json = null;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;

        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->json = $decoded;
            }
        }
    }

    public function getMethod(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($this->post['_method'])) {
            return strtoupper($this->post['_method']);
        }
        return $method;
    }

    public function getPath(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Auto detect script name directory to support running in subfolders e.g. /penjualan_cafe/public/ or /penjualan_cafe/
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName); // e.g. /penjualan_cafe/public or /penjualan_cafe

        if ($baseDir !== '/' && $baseDir !== '\\' && !empty($baseDir)) {
            $baseDir = str_replace('\\', '/', $baseDir);
            if (str_starts_with($path, $baseDir)) {
                $path = substr($path, strlen($baseDir));
            }
        }

        // Also handle if base folder without public is used
        $parentDir = dirname($baseDir);
        if ($parentDir !== '/' && $parentDir !== '\\' && !empty($parentDir)) {
            $parentDir = str_replace('\\', '/', $parentDir);
            if (str_starts_with($path, $parentDir)) {
                $path = substr($path, strlen($parentDir));
            }
        }

        if (empty($path)) {
            $path = '/';
        }

        return '/' . ltrim($path, '/');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->json !== null && array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        return $default;
    }

    public function all(): array
    {
        if ($this->json !== null) {
            return array_merge($this->get, $this->post, $this->json);
        }
        return array_merge($this->get, $this->post);
    }

    public function isJson(): bool
    {
        return $this->json !== null;
    }

    public function getRawBody(): string
    {
        return file_get_contents('php://input');
    }

    public function getHeader(string $name): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$headerKey] ?? null;
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
