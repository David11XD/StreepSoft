<?php
declare(strict_types=1);

/** Genera los reportes generales con la misma identidad visual del historial de pagos. */
class PdfExportador implements FormatoExportador
{
    public function exportar(string $titulo, array $columnas, array $filas): void
    {
        $e = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
        $logo = $this->logoEmbebido();

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>'
            . '@page { margin: 28px 32px 44px 32px; }'
            . 'body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #222222; }'
            . 'table { border-collapse: collapse; }'
            . '.encabezado { width: 100%; background-color: #000000; border-bottom: 4px solid #FFD700; }'
            . '.encabezado td { padding: 12px 16px; vertical-align: middle; }'
            . '.encabezado h1 { margin: 0; font-size: 18px; color: #FFD700; }'
            . '.encabezado p { margin: 3px 0 0; font-size: 10px; color: #E8E8E8; }'
            . '.titulo { margin: 16px 0 4px; font-size: 15px; color: #222222; }'
            . '.generado { margin: 0 0 12px; font-size: 8px; color: #666666; }'
            . '.resumen { width: 100%; margin-top: 10px; }'
            . '.resumen td { padding: 8px 10px; border: 1px solid #D4AF37; background: #FAF6E8; }'
            . '.etiqueta { font-size: 7.5px; color: #7A6410; text-transform: uppercase; }'
            . '.valor { margin-top: 2px; font-size: 11px; font-weight: bold; color: #111111; }'
            . '.reporte { width: 100%; margin-top: 12px; }'
            . '.reporte thead { display: table-header-group; }'
            . '.reporte th { background: #000000; color: #FFD700; border: 1px solid #D4AF37; padding: 7px 6px; text-align: left; font-size: 8px; }'
            . '.reporte td { border: 1px solid #CCCCCC; padding: 6px; vertical-align: top; overflow-wrap: break-word; }'
            . '.reporte tr { page-break-inside: avoid; }'
            . '.reporte tbody tr:nth-child(even) td { background: #F2F2F2; }'
            . '.vacio { text-align: center; padding: 18px; color: #666666; }'
            . '</style></head><body>';

        $html .= '<table class="encabezado"><tr>';
        if ($logo !== '') {
            $html .= '<td style="width:64px;"><img src="' . $logo . '" style="height:84px;" alt=""></td>';
        }
        $html .= '<td><h1>COP COLOMBIA INTERNATIONAL</h1><p>Reportes administrativos</p></td></tr></table>';
        $html .= '<h2 class="titulo">' . $e($titulo) . '</h2>'
            . '<p class="generado">Generado el ' . $e(date('d-m-Y H:i')) . ' · Registros: ' . count($filas) . '</p>';

        $html .= '<table class="reporte"><thead><tr>';
        foreach ($columnas as $columna) {
            $html .= '<th>' . $e($columna) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if ($filas === []) {
            $html .= '<tr><td class="vacio" colspan="' . max(1, count($columnas)) . '">No hay registros para mostrar.</td></tr>';
        } else {
            foreach ($filas as $fila) {
                $html .= '<tr>';
                foreach ($columnas as $indice => $columna) {
                    $valor = is_array($fila) ? ($fila[$columna] ?? $fila[$indice] ?? '') : '';
                    if (is_array($valor) || is_object($valor)) $valor = '';
                    $html .= '<td>' . $e($valor) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table></body></html>';

        $opciones = new \Dompdf\Options();
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('defaultFont', 'DejaVu Sans');
        $opciones->set('chroot', realpath(__DIR__ . '/../..'));
        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->addInfo('Title', $titulo);
        $dompdf->addInfo('Author', 'COP COLOMBIA INTERNATIONAL');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text($canvas->get_width() - 120, $canvas->get_height() - 28,
            'Página {PAGE_NUM} de {PAGE_COUNT}', $fuente, 8, [0.4, 0.4, 0.4]);
        $dompdf->stream($this->nombreArchivo($titulo), ['Attachment' => true]);
        exit;
    }

    private function logoEmbebido(): string
    {
        $ruta = __DIR__ . '/../../public/Image/logo.png';
        if (!is_file($ruta) || !is_readable($ruta)) return '';
        $contenido = @file_get_contents($ruta);
        return $contenido === false ? '' : 'data:image/png;base64,' . base64_encode($contenido);
    }

    private function nombreArchivo(string $titulo): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $titulo);
        $slug = strtolower((string) $slug);
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '_', $slug), '_');
        return ($slug !== '' ? $slug : 'reporte') . '.pdf';
    }
}