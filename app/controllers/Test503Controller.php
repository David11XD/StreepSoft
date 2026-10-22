<?php
class Test503Controller extends Controller
{
    public function error(): void
    {
        http_response_code(503);
        require_once APP_PATH . '/views/errores/503.php';
        exit;
    }
}