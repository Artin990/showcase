<?php

/**
 * کلاس پایه Controller - سایر کنترلرها از این کلاس ارث‌بری می‌کنند
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function view(string $viewPath, array $data = [], string $layout = 'layouts/main'): void
    {
        view($viewPath, $data, $layout);
    }

    protected function input(string $key, $default = '')
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /** بررسی توکن CSRF برای فرم‌های POST - در صورت نامعتبر بودن، متوقف می‌شود */
    protected function verifyCsrfOrDie(): void
    {
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            die('درخواست نامعتبر است (CSRF). لطفا صفحه را رفرش کرده و دوباره تلاش کنید.');
        }
    }
}
