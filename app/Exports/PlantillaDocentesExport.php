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
            [
                'DOC002',
                'María',
                'López Mendoza',
                '87654321',
                '77654321',
                'maria.lopez@ficct.uagrm.edu.bo'
            ],
            [
                'DOC003',
                'Carlos',
                'Rodríguez Torres',
                '11223344',
                '77998877',
                ''
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

        // Agregar notas en las filas de ejemplo
        $sheet->getComment('A2')->getText()->createTextRun('Ejemplo 1: Con todos los datos');
        $sheet->getComment('A3')->getText()->createTextRun('Ejemplo 2: Con email institucional');
        $sheet->getComment('A4')->getText()->createTextRun('Ejemplo 3: Sin email (se genera automáticamente)');

        // Agregar instrucciones en una hoja separada (nota al final)
        $sheet->setCellValue('A6', 'INSTRUCCIONES:');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A7', '1. CODIGO: Código único del docente (Obligatorio)');
        $sheet->setCellValue('A8', '2. NOMBRE: Nombre(s) del docente (Obligatorio)');
        $sheet->setCellValue('A9', '3. APELLIDOS: Apellidos del docente (Obligatorio)');
        $sheet->setCellValue('A10', '4. CI: Cédula de identidad (Obligatorio - Se usa para generar la contraseña)');
        $sheet->setCellValue('A11', '5. TELEFONO: Número de teléfono (Opcional)');
        $sheet->setCellValue('A12', '6. EMAIL: Si se deja vacío, se generará automáticamente como: nombre.apellidos@ficct.uagrm.edu.bo');
        $sheet->setCellValue('A13', '');
        $sheet->setCellValue('A14', 'GENERACIÓN AUTOMÁTICA DE CONTRASEÑA:');
        $sheet->getStyle('A14')->getFont()->setBold(true)->setSize(12)->setColor(new Color('0070C0'));
        $sheet->setCellValue('A15', '- La contraseña se genera automáticamente como: Primera letra del NOMBRE + CI');
        $sheet->setCellValue('A16', '- Ejemplo: Juan Velarde con CI 5485555 → Contraseña: J5485555');
        $sheet->setCellValue('A17', '- María López con CI 7894561 → Contraseña: M7894561');
        $sheet->setCellValue('A18', '');
        $sheet->setCellValue('A19', 'IMPORTANTE:');
        $sheet->getStyle('A19')->getFont()->setBold(true)->setColor(new Color('FF0000'));
        $sheet->setCellValue('A20', '- Elimine las filas de ejemplo antes de importar');
        $sheet->setCellValue('A21', '- No modifique los nombres de las columnas');
        $sheet->setCellValue('A22', '- Los campos CODIGO, NOMBRE, APELLIDOS y CI son obligatorios');
        $sheet->setCellValue('A23', '- El sistema generará automáticamente el username basado en nombre y apellidos');

        return [];
    }
}
