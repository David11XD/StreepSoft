<?php
declare(strict_types=1);


/**PdfExportador
 * Implementa FormatoExportador para generar archivos .pdf usando dompdf.*/
class PdfExportador implements FormatoExportador
{
    public function exportar(string $titulo, array $columnas, array $filas): void
    {
        // Crear el HTML inicial: aquí solo va HTML y CSS.
        $html = <<<'HTML'
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #222222;
                    font-size: 10px;
                }

                .encabezado {
                    background-color: #000000;
                    color: #FFD700;
                    text-align: center;
                    padding: 22px;
                    border-bottom: 4px solid #FFD700;
                    flex-direction: row;
                    display: flex;
                    gap: 15px;
                }

                .Logo{
                    background-image: url('/streepsoft/public/Image/logo.png');
                    background-size: contain;
                    background-repeat: no-repeat;
                    width: 30px;
                    height: 25px;
                }

                .encabezado h1 {
                    font-size: 22px;
                    margin: 0;
                }

                h2 {
                    color: #222222;
                    margin-top: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }

                thead th {
                    background-color: #000000;
                    color: #FFD700;
                    border: 1px solid #D4AF37;
                    padding: 8px;
                    text-align: left;
                }

                tbody td {
                    border: 1px solid #CCCCCC;
                    padding: 7px;
                }

                tbody tr:nth-child(even) {
                    background-color: #F2F2F2;
                }
            </style>
        </head>
        <body>
            <div class="encabezado">
                <h1>COP COLOMBIA INTERNATIONAL</h1>
            </div>
        HTML;
        // Agregar el título del reporte.
        $html .= '<h2>' .
            htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') .
            '</h2>';

        // Construir la tabla con los encabezados.
        $html .= '<table><thead><tr>';

        foreach ($columnas as $columna) {
            $html .= '<th>' .
                htmlspecialchars((string) $columna, ENT_QUOTES, 'UTF-8') .
                '</th>';
        }

        $html .= '</tr></thead><tbody>';
        

        // Agregar las filas de datos.
        foreach ($filas as $fila) {
            $html .= '<tr>';

            foreach ($columnas as $indice => $columna) {
                // Compatible con filas asociativas o indexadas.
                $valor = $fila[$columna] ?? $fila[$indice] ?? '';

                if (is_array($valor) || is_object($valor)) {
                    $valor = '';
                }

                $html .= '<td>' .
                    htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') .
                    '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        // Generar y descargar el PDF una sola vez.
        // Configurar Dompdf para cargar imágenes locales.
        $opciones = new \Dompdf\Options();
        $opciones->set('isRemoteEnabled', true);
        $opciones->set('chroot', realpath(__DIR__ . '/../..'));

        // Crear el PDF.
        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream($this->nombreArchivo($titulo), [
            'Attachment' => true
        ]);

        exit;
    }

    /** Convierte el título en un nombre de archivo seguro. */
    private function nombreArchivo(string $titulo): string
    {
        $slug = strtolower(str_replace(' ', '_', $titulo));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);

        return ($slug !== '' ? $slug : 'reporte') . '.pdf';
    }
}