<?php

declare(strict_types=1);

/**
 * ReporteController
 * Este controlador es el ÚNICO punto de entrada para descargar cualquier
 * reporte del sistema, en cualquier formato (Excel, PDF, Word).*/

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../reportes/ReporteDatos.php';
require_once __DIR__ . '/../reportes/FormatoExportador.php';
require_once __DIR__ . '/../reportes/ReportePagos.php';
require_once __DIR__ . '/../reportes/ReporteJugadores.php';
require_once __DIR__ . '/../reportes/ReporteDeudas.php';
require_once __DIR__ . '/../reportes/ExcelExportador.php';
require_once __DIR__ . '/../reportes/PdfExportador.php';
require_once __DIR__ . '/../reportes/WordExportador.php';
class ReporteController extends Controller
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Punto de entrada único: /reportes/generar?tipo=pagos&formato=excel*/
    public function generar(): void
    {
        $tipo = $_GET['tipo'] ?? '';
        $formato = $_GET['formato'] ?? '';

        $reporte = $this->obtenerReporte($tipo);
        $exportador = $this->obtenerExportador($formato);

        // Si el tipo o el formato no existen (o vinieron vacíos/mal escritos
        // desde la URL), no seguimos: devolvemos un error claro.
        if (!$reporte || !$exportador) {
            http_response_code(400);
            echo "Reporte o formato no soportado.";
            return;
        }

        // El controlador NO sabe si esto termina en un .xlsx, un .pdf o un
        // .docx — solo le pasa los datos genéricos (título, columnas, filas)
        // y confía en que el exportador sabe qué hacer con ellos.
        $exportador->exportar(
            $reporte->getTitulo(),
            $reporte->getColumnas(),
            $reporte->getFilas()
        );
    }

    /**"Fábrica" de reportes: traduce el string que llega por la URL
     * (?tipo=pagos) en la clase concreta que sabe armar esos datos. */
    private function obtenerReporte(string $tipo): ?ReporteDatos
    {
        return match ($tipo) {
            'pagos' => new ReportePagos(new Deuda($this->pdo)),
            'jugadores' => new ReporteJugadores(new Jugador($this->pdo)),
            'deudas' => new ReporteDeudas(new Deuda($this->pdo)),
            default => null, // tipo desconocido -> no hay reporte que devolver
        };
    }

    /**"Fábrica" de exportadores: traduce el string que llega por la URL
     * (?formato=excel) en la clase concreta que sabe generar ese archivo.*/
    private function obtenerExportador(string $formato): ?FormatoExportador
    {
        return match ($formato) {
            'excel' => new ExcelExportador(),
            'pdf' => new PdfExportador(),
            'word' => new WordExportador(),
            default => null, // formato desconocido -> no hay exportador que devolver
        };
    }
}
