<?php

/**
 * روتر ساده - مسیرها را به Controller و متد مربوطه متصل می‌کند
 */
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // حذف زیرپوشه در صورت نصب روی ساب‌فولدر (مثلا وقتی سایت روی localhost/invoice-app اجرا می‌شود)
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $uri = '/' . trim($uri, '/');

        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler === null) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        call_user_func($handler);
    }
}
