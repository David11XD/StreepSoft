<?php
class TestController extends Controller
{
    public function error(): void
    {
        throw new Exception("Error de prueba");
    }
}