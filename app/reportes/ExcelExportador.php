<?php
declare(strict_types=1);

/**Herramientas de PhpSpreadsheet para la gestión de archivos de Excel.*/
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Representa el archivo de Excel en memoria (crea hojas, celdas y estilos).
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Herramienta para guardar y transformar los datos de la memoria en un archivo físico de Excel (.xlsx).
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // Traductor que convierte letras de columnas (como 'A', 'B') a números (1, 2) para facilitar el uso de bucles.

/**ExcelExportador
 * Implementa FormatoExportador para generar archivos .xlsx usando la
 * librería PhpSpreadsheet.*/
class ExcelExportador implements FormatoExportador
{
    public function exportar(string $titulo, array $columnas, array $filas): void
    {
        // Crea el archivo en memoria y toma su hoja activa
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle($this->tituloValido($titulo));

        // Escribe la fila de encabezados (fila 1), recorriendo $columnas en vez de escribirlas a mano 
        $numColumna = 1;
        foreach ($columnas as $nombreColumna) {
            $columnaLetra = Coordinate::stringFromColumnIndex($numColumna);
            $hoja->setCellValue($columnaLetra . '1', $nombreColumna);
            $numColumna++;
        }

        // Escribe las filas de datos, empezando en la fila 2 porque
        //    la fila 1 ya la ocupamos con los encabezados
        $numFila = 2;

        foreach ($filas as $fila) {
            $numColumna = 1;

            foreach ($fila as $valor) {
                $columnaLetra = Coordinate::stringFromColumnIndex($numColumna);

                $hoja->setCellValue($columnaLetra . $numFila, $valor);

                $numColumna++;
            }

            $numFila++;
        }

        //  encabezados en negrita
        $ultimaColumna = Coordinate::stringFromColumnIndex(count($columnas));
        $hoja->getStyle('A1:' . $ultimaColumna . '1')->getFont()->setBold(true);

        // Enviar el archivo al navegador como descarga 
        $nombreArchivo = $this->nombreArchivo($titulo);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output'); // "guarda" el archivo directo hacia la respuesta HTTP
        exit;
    }

    /**Excel no permite ciertos caracteres en el nombre de la hoja (como
     * ':' o '/') ni nombres de más de 31 caracteres. Esta pequeña
     * limpieza evita que el reporte falle por un título largo o con
     * caracteres raros.*/
    private function tituloValido(string $titulo): string
    {
        $limpio = str_replace([':', '/', '\\', '?', '*', '[', ']'], '-', $titulo);
        return substr($limpio, 0, 31);
    }

    /** Convierte el título del reporte en un nombre de archivo seguro para descargar */
    private function nombreArchivo(string $titulo): string
    {
        $slug = strtolower(str_replace(' ', '_', $titulo));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        return $slug . '.xlsx';
    }
}