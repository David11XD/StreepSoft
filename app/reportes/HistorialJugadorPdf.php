<?php
declare(strict_types=1);

/**
 * HistorialJugadorPdf
 *
 * Genera el PDF con TODOS los pagos registrados de un jugador: la misma información de la
 * pantalla "Historial de pagos" (jugadores/deudas/historial/:id), pero completa, sin el filtro
 * de fechas ni la paginación. Usa Dompdf, igual que PdfExportador.
 *
 * Requiere que vendor/autoload.php ya esté cargado (lo hace DeudaController).
 */
class HistorialJugadorPdf
{
    /**
     * Devuelve los bytes del PDF.
     *
     * @param array $jugador   Datos de Jugador::obtenerParaHistorial()
     * @param array $historial Pagos de HistorialDeuda::obtenerPorJugador()
     */
    public function generar(array $jugador, array $historial): string
    {
        $nombre = $this->nombreCompleto($jugador);

        $opciones = new \Dompdf\Options();
        $opciones->set('isRemoteEnabled', false);   // el logo va embebido, no se descarga nada
        $opciones->set('defaultFont', 'DejaVu Sans'); // soporta tildes y ñ
        $opciones->set('chroot', realpath(__DIR__ . '/../..'));

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->addInfo('Title', 'Historial de pagos - ' . $nombre);
        $dompdf->addInfo('Author', 'COP COLOMBIA INTERNATIONAL');
        $dompdf->loadHtml($this->construirHtml($jugador, $historial), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Numeración de páginas en el pie (después de render, cuando ya se sabe el total).
        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text(
            $canvas->get_width() - 120,
            $canvas->get_height() - 28,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            $fuente,
            8,
            [0.4, 0.4, 0.4]
        );

        return (string) $dompdf->output();
    }

    /** Nombre de archivo seguro, ej.: historial_pagos_Andres_Perez.pdf */
    public function nombreArchivo(array $jugador): string
    {
        $nombre = strtr($this->nombreCompleto($jugador), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
        $slug = trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $nombre), '_');

        if ($slug === '') {
            $slug = 'jugador_' . (int) ($jugador['id_jugadores'] ?? 0);
        }

        return 'historial_pagos_' . $slug . '.pdf';
    }

    private function construirHtml(array $jugador, array $historial): string
    {
        $nombre = $this->nombreCompleto($jugador);
        $categoria = trim((string) ($jugador['categoria'] ?? ''));
        $estado = trim((string) ($jugador['estado'] ?? ''));

        // Se calculan los pagos igual que en la pantalla (vista historial.php).
        $filas = [];
        $totalPagado = 0.0;
        $ultimoPago = null;

        foreach ($historial as $pago) {
            $matricula = (float) ($pago['matricula'] ?? 0);
            $totalCiclo = (float) ($pago['totalidad'] ?? 0);
            // La mensualidad se almacena separada; respaldo para datos antiguos.
            $mensualidad = (float) ($pago['mensualidad'] ?? max(0, $totalCiclo - $matricula));
            $pagado = (float) ($pago['valor_pagado'] ?? $totalCiclo);
            $concepto = trim((string) ($pago['concepto'] ?? ''));

            $fechaPago = $this->fecha($pago['fecha_pago'] ?? null);
            if (!empty($pago['fecha_pago']) && ($ultimoPago === null || (string) $pago['fecha_pago'] > $ultimoPago)) {
                $ultimoPago = (string) $pago['fecha_pago'];
            }

            $totalPagado += $pagado;
            $filas[] = [
                'limite' => $this->fecha($pago['fecha_limite_pago'] ?? null),
                'pago' => $fechaPago,
                'matricula' => $this->dinero($matricula),
                'mensualidad' => $this->dinero($mensualidad),
                'metodo' => trim((string) ($pago['metodo_pago'] ?? '')) !== '' ? (string) $pago['metodo_pago'] : '-',
                'concepto' => $concepto !== '' ? $concepto : 'Sin concepto',
                'total' => $this->dinero($pagado),
            ];
        }

        $e = static fn (string $texto): string => htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        $logo = $this->logoEmbebido();

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>'
            . '@page { margin: 28px 32px 44px 32px; }'
            . 'body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #222222; }'
            . 'table { border-collapse: collapse; }'
            . '.encabezado { width: 100%; background-color: #000000; border-bottom: 4px solid #FFD700; }'
            . '.encabezado td { padding: 12px 16px; vertical-align: middle; }'
            . '.encabezado h1 { margin: 0; font-size: 18px; color: #FFD700; }'
            . '.encabezado p { margin: 3px 0 0 0; font-size: 10px; color: #E8E8E8; }'
            . '.datos { width: 100%; margin-top: 14px; }'
            . '.datos td { width: 25%; padding: 8px 10px; border: 1px solid #D4AF37; background-color: #FAF6E8; vertical-align: top; }'
            . '.datos .etiqueta { display: block; font-size: 7.5px; color: #7A6410; text-transform: uppercase; }'
            . '.datos .valor { display: block; margin-top: 2px; font-size: 11px; font-weight: bold; color: #111111; }'
            . '.pagos { width: 100%; margin-top: 16px; }'
            . '.pagos thead { display: table-header-group; }'
            . '.pagos th { background-color: #000000; color: #FFD700; border: 1px solid #D4AF37; padding: 7px 6px; text-align: left; font-size: 8.5px; }'
            . '.pagos td { border: 1px solid #CCCCCC; padding: 6px; vertical-align: top; }'
            . '.pagos tr { page-break-inside: avoid; }'
            . '.pagos tbody tr:nth-child(even) td { background-color: #F2F2F2; }'
            . '.der { text-align: right; }'
            . '.vacio { text-align: center; padding: 18px; color: #666666; }'
            . '.nota { margin-top: 10px; font-size: 8px; color: #666666; }'
            . '</style></head><body>';

        $html .= '<table class="encabezado"><tr>';
        if ($logo !== '') {
            $html .= '<td style="width:64px;"><img src="' . $logo . '" style="height:44px;" alt=""></td>';
        }
        $html .= '<td><h1>COP COLOMBIA INTERNATIONAL</h1><p>Historial de pagos del alumno</p></td></tr></table>';

        $html .= '<table class="datos"><tr>'
            . '<td><span class="etiqueta">Alumno</span><span class="valor">' . $e($nombre !== '' ? $nombre : '-') . '</span></td>'
            . '<td><span class="etiqueta">Categoría</span><span class="valor">' . $e($categoria !== '' ? $categoria : '-') . '</span></td>'
            . '<td><span class="etiqueta">Estado</span><span class="valor">' . $e($estado !== '' ? ucfirst($estado) : '-') . '</span></td>'
            . '<td><span class="etiqueta">Generado el</span><span class="valor">' . $e(date('d-m-Y H:i')) . '</span></td>'
            . '</tr><tr>'
            . '<td><span class="etiqueta">Pagos registrados</span><span class="valor">' . count($filas) . '</span></td>'
            . '<td><span class="etiqueta">Total pagado</span><span class="valor">' . $e($this->dinero($totalPagado)) . '</span></td>'
            . '<td colspan="2"><span class="etiqueta">Último pago</span><span class="valor">'
            . $e($ultimoPago !== null ? $this->fecha($ultimoPago) : '-') . '</span></td>'
            . '</tr></table>';

        $html .= '<table class="pagos"><thead><tr>'
            . '<th style="width:11%;">Fecha límite de pago</th>'
            . '<th style="width:11%;">Fecha de pago</th>'
            . '<th class="der" style="width:12%;">Matrícula</th>'
            . '<th class="der" style="width:12%;">Mensualidad</th>'
            . '<th style="width:12%;">Método</th>'
            . '<th style="width:26%;">Concepto</th>'
            . '<th class="der" style="width:16%;">Total pagado</th>'
            . '</tr></thead><tbody>';

        if ($filas === []) {
            $html .= '<tr><td class="vacio" colspan="7">Este jugador todavía no tiene pagos registrados.</td></tr>';
        }

        foreach ($filas as $fila) {
            $html .= '<tr>'
                . '<td>' . $e($fila['limite']) . '</td>'
                . '<td>' . $e($fila['pago']) . '</td>'
                . '<td class="der">' . $e($fila['matricula']) . '</td>'
                . '<td class="der">' . $e($fila['mensualidad']) . '</td>'
                . '<td>' . $e($fila['metodo']) . '</td>'
                . '<td>' . $e($fila['concepto']) . '</td>'
                . '<td class="der"><strong>' . $e($fila['total']) . '</strong></td>'
                . '</tr>';
        }

        $html .= '</tbody></table>'
            . '<p class="nota">Incluye todos los pagos registrados del alumno, del más reciente al más antiguo.</p>'
            . '</body></html>';

        return $html;
    }

    private function nombreCompleto(array $jugador): string
    {
        return trim(((string) ($jugador['nombres'] ?? '')) . ' ' . ((string) ($jugador['apellidos'] ?? '')));
    }

    /** Fecha como d-m-Y (igual que la pantalla); '-' si no hay fecha válida. */
    private function fecha($valor): string
    {
        if (empty($valor)) {
            return '-';
        }

        $fecha = date_create((string) $valor);

        return $fecha ? $fecha->format('d-m-Y') : '-';
    }

    /** Dinero con el mismo formato de la pantalla: $1.250.000 cop */
    private function dinero(float $valor): string
    {
        return '$' . number_format($valor, 0, ',', '.') . ' cop';
    }

    /**
     * Emblema del club como data URI (así Dompdf no depende de rutas ni de red).
     * El archivo original pesa ~2 MB: se recorta el margen transparente y se reduce para
     * que el PDF no se infle. Devuelve '' si no se puede usar (sin GD o sin archivo).
     */
    private function logoEmbebido(): string
    {
        $ruta = __DIR__ . '/../../public/Image/logo.png';

        if (!extension_loaded('gd') || !is_file($ruta)) {
            return '';
        }

        $origen = @imagecreatefrompng($ruta);
        if ($origen === false) {
            return '';
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        if ($ancho <= 0 || $alto <= 0) {
            return '';
        }

        // Recuadro del dibujo (se ignora el margen transparente), medido sobre una copia
        // pequeña para que sea rápido aunque el archivo original sea grande.
        $miniAncho = 320;
        $miniAlto = max(1, (int) round($alto * $miniAncho / $ancho));
        $mini = imagecreatetruecolor($miniAncho, $miniAlto);
        imagealphablending($mini, false);
        imagesavealpha($mini, true);
        imagefill($mini, 0, 0, imagecolorallocatealpha($mini, 0, 0, 0, 127));
        imagecopyresampled($mini, $origen, 0, 0, 0, 0, $miniAncho, $miniAlto, $ancho, $alto);

        $minX = $miniAncho;
        $minY = $miniAlto;
        $maxX = -1;
        $maxY = -1;
        for ($y = 0; $y < $miniAlto; $y++) {
            for ($x = 0; $x < $miniAncho; $x++) {
                $alfa = (imagecolorat($mini, $x, $y) >> 24) & 0x7F; // 0 = opaco, 127 = transparente
                if ($alfa < 100) {
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        // Zona de origen: el dibujo con un pequeño margen; si no se detecta, la imagen completa.
        $origenX = 0;
        $origenY = 0;
        $origenAncho = $ancho;
        $origenAlto = $alto;
        if ($maxX >= $minX && $maxY >= $minY) {
            $factor = $ancho / $miniAncho;
            $origenX = max(0, (int) floor(($minX - 1) * $factor));
            $origenY = max(0, (int) floor(($minY - 1) * $factor));
            $origenAncho = min($ancho - $origenX, (int) ceil(($maxX - $minX + 3) * $factor));
            $origenAlto = min($alto - $origenY, (int) ceil(($maxY - $minY + 3) * $factor));
        }

        $nuevoAlto = 140;
        $nuevoAncho = max(1, (int) round($origenAncho * $nuevoAlto / max(1, $origenAlto)));

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagefill($destino, 0, 0, imagecolorallocatealpha($destino, 0, 0, 0, 127));
        imagecopyresampled(
            $destino, $origen, 0, 0, $origenX, $origenY,
            $nuevoAncho, $nuevoAlto, $origenAncho, $origenAlto
        );

        ob_start();
        imagepng($destino);
        $png = (string) ob_get_clean();

        return $png === '' ? '' : 'data:image/png;base64,' . base64_encode($png);
    }
}
