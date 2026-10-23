<?php
declare(strict_types=1);

class PagosController extends Controller
{
    public function matriz(): void
    {
        $this->view('pagos/matriz');
    }
}