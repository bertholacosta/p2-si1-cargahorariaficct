<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PlantillaDocentesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Datos de ejemplo para la plantilla
     * Estos ejemplos deben ser reemplazados con los datos reales
     */
    public function collection()
    {
        return collect([
            [
                'DOC001',
                'Juan',
                'Pérez García',
                '12345678',
                '77123456',
                'juan.perez@ficct.uagrm.edu.bo'
            ],
        ]);
    }

    /**
     * Encabezados de la plantilla
     */
    public function headings(): array
    {
        return [
            'CODIGO',
            'NOMBRE',
            'APELLIDOS',
            'CI',
            'TELEFONO',
            'EMAIL'
        ];
    }

    /**
     * Estilos de la plantilla
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        return [];
    }
}
