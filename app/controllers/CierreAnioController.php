<?php
declare(strict_types=1);

class CierreAnioController extends Controller
{
    public function index(): void
    {
        $this->view('cierreAnio/cierreAnio');
    }
}