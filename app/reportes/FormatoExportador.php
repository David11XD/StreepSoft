<?php
declare(strict_types=1);

/**FormatoExportador */
interface FormatoExportador
{
    /**Genera el archivo con los datos recibidos y lo envía directamente
     * al navegador como descarga (usa header() + guarda en 'php://output').
     * Por eso no retorna nada: su trabajo termina en enviar la respuesta HTTP.
     */
    public function exportar(string $titulo, array $columnas, array $filas): void;
}