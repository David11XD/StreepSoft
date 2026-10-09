<?php
declare(strict_types=1);

/**PdfExportador
 * Implementa FormatoExportador para generar archivos .pdf usando dompdf.*/
class PdfExportador implements FormatoExportador
{
    public function exportar(string $titulo, array $columnas, array $filas): void
    {
        // Encabezado del documento + apertura de la tabla
        $html = '<h2>' . htmlspecialchars($titulo) . '</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';

        // Fila de encabezados, recorriendo $columnas (igual que en Excel,
        //    pero generando <th> en vez de escribir celdas)
        $html .= '<tr>';
        foreach ($columnas as $columna) {
            $html .= '<th>' . htmlspecialchars($columna) . '</th>';
        }
        $html .= '</tr>';

        // Filas de datos: un <tr> por registro, un <td> por valor.
        foreach ($filas as $fila) {
            $html .= '<tr>';

            foreach ($fila as $valor) {
                $html .= '<td>' . htmlspecialchars((string) $valor) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';

        // Generar el PDF a partir del HTML armado arriba
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // horizontal, porque hay varias columnas
        $dompdf->render();

        // Enviar como descarga. A diferencia de Excel, aquí NO usamos
        //    header() ni php://output a mano: stream() con 'Attachment' => true
        //    ya hace ese trabajo internamente.
        $nombreArchivo = $this->nombreArchivo($titulo);
        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    }

    /** Convierte el título del reporte en un nombre de archivo seguro para descargar */
    private function nombreArchivo(string $titulo): string
    {
        $slug = strtolower(str_replace(' ', '_', $titulo));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        return $slug . '.pdf';
    }
}