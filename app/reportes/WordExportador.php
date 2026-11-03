<?php

declare(strict_types=1);

/**
 * WordExportador
 * Implementa FormatoExportador para generar archivos .docx usando la
 * librería PhpWord.*/
class WordExportador implements FormatoExportador
{
    public function exportar(string $titulo, array $columnas, array $filas): void
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord(); // Crear un nuevo documento Word
        $section = $phpWord->addSection(); // Agregar una sección al documento
        $section->addTitle($titulo, 1); // Agregar el título del reporte

        // Agregar la tabla con los datos
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);

        // Agregar la fila de encabezados
        $table->addRow();
        foreach ($columnas as $columna) {
            $table->addCell(2000)->addText($columna, ['bold' => true]);
        }

        // Agregar las filas de datos
        foreach ($filas as $fila) {
            $table->addRow();
            foreach ($fila as $valor) {
                $table->addCell()->addText((string) $valor);
            }
        }

        // Enviar el archivo al navegador como descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $this->nombreArchivo($titulo) . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }

    /** Convierte el título del reporte en un nombre de archivo seguro para descargar */
    private function nombreArchivo(string $titulo): string
    {
        $slug = strtolower(str_replace(' ', '_', $titulo));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug);
        return $slug . '.docx';
    }
}