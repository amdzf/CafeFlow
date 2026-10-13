<?php

namespace Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Helpers\Csrf;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middlewares): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Filter string keys for parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middlewares
                $this->runMiddlewares($route['middlewares'], $request);

                // Execute handler
                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($request, ...array_values($params));
                    return;
                }

                if (is_callable($handler)) {
                    call_user_func($handler, $request, ...array_values($params));
                    return;
                }
            }
        }

        // 404 handler
        http_response_code(404);
        if ($request->isJson() || $request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Endpoint tidak ditemukan.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        View::render('errors/404', ['title' => 'Halaman Tidak Ditemukan - CafeFlow POS'], 'auth');
    }

    private function runMiddlewares(array $middlewares, Request $request): void
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === 'auth') {
                AuthMiddleware::handle($request);
            } elseif (str_starts_with($middleware, 'role:')) {
                $rolesStr = substr($middleware, 5);
                $roles = explode(',', $rolesStr);
                RoleMiddleware::handle($request, array_map('trim', $roles));
            } elseif ($middleware === 'csrf') {
                if ($request->getMethod() === 'POST' || $request->getMethod() === 'PUT' || $request->getMethod() === 'DELETE') {
                    $token = $request->input('csrf_token') ?? $request->getHeader('X-CSRF-TOKEN');
                    if (!Csrf::validate($token)) {
                        if ($request->isJson() || $request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
                            http_response_code(403);
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.'], JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                        set_flash('error', 'Token keamanan kedaluwarsa. Silakan ulangi kembali.');
                        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('/')));
                        exit;
                    }
                }
            }
        }
    }
}
