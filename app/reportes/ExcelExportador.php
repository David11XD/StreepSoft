<?php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ExcelExportador implements FormatoExportador
{
    public function exportar(
        string $titulo,
        array $columnas,
        array $filas
    ): void {
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $hoja->setTitle($this->tituloValido($titulo));

        $totalColumnas = count($columnas);

        if ($totalColumnas === 0) {
            throw new RuntimeException(
                'El reporte no tiene columnas para exportar.'
            );
        }

        $ultimaColumna = Coordinate::stringFromColumnIndex(
            $totalColumnas
        );

        // 1. Encabezado institucional
        $hoja->mergeCells("A1:{$ultimaColumna}2");
        $hoja->setCellValue(
            'A1',
            'COP COLOMBIA INTERNATIONAL'
        );

        $hoja->getRowDimension(1)->setRowHeight(30);
        $hoja->getRowDimension(2)->setRowHeight(20);

        $hoja->getStyle("A1:{$ultimaColumna}2")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 18,
                'color' => ['rgb' => 'FFD700'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // 2. Título del reporte
        $hoja->mergeCells("A3:{$ultimaColumna}3");
        $hoja->setCellValue('A3', $titulo);
        $hoja->getRowDimension(3)->setRowHeight(25);

        $hoja->getStyle("A3:{$ultimaColumna}3")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 13,
                'color' => ['rgb' => '222222'],
            ],
            'alignment' => [
                'horizontal' => 'left',
                'vertical' => 'center',
            ],
        ]);

        // 3. Encabezados de las columnas
        $filaEncabezado = 5;
        $hoja->getRowDimension(4)->setRowHeight(8);

        foreach ($columnas as $indice => $nombre) {
            $letra = Coordinate::stringFromColumnIndex(
                $indice + 1
            );

            $hoja->setCellValue(
                $letra . $filaEncabezado,
                $nombre
            );
        }

        $hoja->getStyle(
            "A{$filaEncabezado}:{$ultimaColumna}{$filaEncabezado}"
        )->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '292929'],
            ],
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'color' => ['rgb' => 'FFD700'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrapText' => true,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => 'FFD700'],
                ],
            ],
        ]);

        $hoja->getRowDimension($filaEncabezado)->setRowHeight(32);

        // 4. Datos y filas alternadas
        $filaActual = $filaEncabezado + 1;

        foreach ($filas as $fila) {
            foreach ($columnas as $indice => $nombre) {
                $letra = Coordinate::stringFromColumnIndex(
                    $indice + 1
                );

                $valor = array_key_exists($indice, $fila)
                    ? $fila[$indice]
                    : '';

                $hoja->setCellValue(
                    $letra . $filaActual,
                    $valor
                );
            }

            $color = ($filaActual % 2 === 0)
                ? 'FFFFFF'
                : 'F1F3F5';

            $hoja->getStyle(
                "A{$filaActual}:{$ultimaColumna}{$filaActual}"
            )->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color],
                ],
                'font' => [
                    'name' => 'Arial',
                    'size' => 10,
                    'color' => ['rgb' => '222222'],
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color' => ['rgb' => 'D9DEE3'],
                    ],
                ],
                'alignment' => [
                    'vertical' => 'center',
                ],
            ]);

            $hoja->getRowDimension($filaActual)->setRowHeight(22);
            $filaActual++;
        }

        // 5. Ajustes de lectura
        for ($i = 1; $i <= $totalColumnas; $i++) {
            $letra = Coordinate::stringFromColumnIndex($i);
            $hoja->getColumnDimension($letra)->setAutoSize(true);
        }

        $hoja->freezePane('A6');

        if ($filaActual > $filaEncabezado + 1) {
            $hoja->setAutoFilter(
                "A{$filaEncabezado}:{$ultimaColumna}" .
                ($filaActual - 1)
            );
        }

        // 6. Configuración de impresión
        $hoja->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $hoja->getPageSetup()
            ->setFitToWidth(1);

        $hoja->getPageSetup()
            ->setFitToHeight(0);

        $hoja->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4);

        $hoja->getPageSetup()->setFitToPage(true);

        $hoja->getPageMargins()->setTop(0.4);
        $hoja->getPageMargins()->setBottom(0.4);
        $hoja->getPageMargins()->setLeft(0.25);
        $hoja->getPageMargins()->setRight(0.25);

        // 7. Descargar el archivo
        $nombreArchivo = $this->nombreArchivo($titulo);

        header(
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header(
            'Content-Disposition: attachment; filename="' .
            $nombreArchivo . '"'
        );
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $spreadsheet->disconnectWorksheets();
        exit;
    }

    private function tituloValido(string $titulo): string
    {
        $limpio = str_replace(
            [':', '/', '\\', '?', '*', '[', ']'],
            '-',
            $titulo
        );

        return substr($limpio ?: 'Reporte', 0, 31);
    }

    private function nombreArchivo(string $titulo): string
    {
        $slug = strtolower(str_replace(' ', '_', $titulo));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);

        return ($slug ?: 'reporte') . '.xlsx';
    }
}
