<?php
class Test400Controller extends Controller
{
    public function error(): void
    {
        http_response_code(400);
        require_once APP_PATH . '/views/errores/400.php';
        exit;
    }
}