<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PlantillaAsignacionesExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $gestion;

    public function __construct($gestion)
    {
        $this->gestion = $gestion;
    }

    /**
     * Datos de ejemplo en la plantilla
     */
    public function array(): array
    {
        return [
            // Fila de ejemplo 1 - Clase que se repite 3 días
            [
                'MAT-101',           // codigo_materia
                '1',                 // numero_grupo
                'DOC001',            // codigo_docente (o AUTO para asignación automática)
                'Lunes, Miércoles, Viernes',  // dias (separados por comas)
                '07:00',             // hora_inicio
                '08:30',             // hora_fin
                'A-101',             // codigo_aula
            ],
            // Fila de ejemplo 2 - Clase que se repite 2 días
            [
                'MAT-102',
                '1',
                'AUTO',              // Asignación automática
                'Martes, Jueves',    // dias múltiples
                '07:45',
                '09:15',
                'A-102',
            ],
            // Fila de ejemplo 3 - Clase de un solo día
            [
                'FIS-201',
                '2',
                'DOC002',
                'Lunes',             // un solo día
                '09:15',
                '11:45',
                'B-201',
            ],
            // Filas vacías para que el usuario complete
            ['', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
            ['', '', '', '', '', '', ''],
        ];
    }

    /**
     * Encabezados de la plantilla
     */
    public function headings(): array
    {
        return [
            'CÓDIGO_MATERIA',
            'NÚMERO_GRUPO',
            'CÓDIGO_DOCENTE',
            'DÍA',
            'HORA_INICIO',
            'HORA_FIN',
            'CÓDIGO_AULA',
        ];
    }

    /**
     * Estilos de la plantilla
     */
    public function styles(Worksheet $sheet)
    {
        // Título de la plantilla
        $sheet->insertNewRowBefore(1, 3);
        $sheet->mergeCells('A1:G1');
        $tituloGestion = "{$this->gestion->año} - Semestre {$this->gestion->semestre}";
        $sheet->setCellValue('A1', "PLANTILLA DE IMPORTACIÓN DE ASIGNACIONES - {$tituloGestion}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F46E5');
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFFFF');
        
        // Instrucciones
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Instrucciones: Complete los datos según los códigos registrados en el sistema. Use "AUTO" en CÓDIGO_DOCENTE para asignación automática. Puede especificar múltiples días separados por comas (ejemplo: Lunes, Miércoles, Viernes).');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6');
        $sheet->getStyle('A2')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(2)->setRowHeight(40);
        
        // Línea vacía
        $sheet->mergeCells('A3:G3');
        
        // Encabezados (ahora en fila 4)
        $sheet->getStyle('A4:G4')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A4:G4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF6366F1');
        $sheet->getStyle('A4:G4')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Ejemplos con color diferente (filas 5-7)
        $sheet->getStyle('A5:G7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
        
        // Agregar instrucciones detalladas
        $this->agregarInstrucciones($sheet);
        
        return [];
    }

    /**
     * Ancho de las columnas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18, // CÓDIGO_MATERIA
            'B' => 15, // NÚMERO_GRUPO
            'C' => 18, // CÓDIGO_DOCENTE
            'D' => 30, // DÍA (más ancho para múltiples días)
            'E' => 15, // HORA_INICIO
            'F' => 15, // HORA_FIN
            'G' => 15, // CÓDIGO_AULA
        ];
    }

    /**
     * Agregar instrucciones y ejemplos de códigos válidos
     */
    private function agregarInstrucciones(Worksheet $sheet)
    {
        $row = 15;
        
        // IMPORTANTE
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'IMPORTANTE:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getFont()->getColor()->setARGB('FFDC2626');
        $row++;
        
        // Instrucciones detalladas
        $sheet->mergeCells("A{$row}:G{$row}");
        $instrucciones = 
            "• Los códigos deben existir previamente en el sistema\n" .
            "• Use 'AUTO' en CÓDIGO_DOCENTE para asignación automática según disponibilidad\n" .
            "• Días: Puede especificar uno o múltiples días separados por comas (Lunes, Martes, Miércoles, Jueves, Viernes, Sábado)\n" .
            "• Ejemplos de días: 'Lunes' (un solo día) o 'Lunes, Miércoles, Viernes' (se creará la misma asignación para los 3 días)\n" .
            "• Formato de hora: HH:MM (ejemplo: 07:00, 08:30)\n" .
            "• Las horas deben coincidir con los módulos de 45 minutos que empiezan desde las 07:00\n" .
            "• Módulos válidos: 07:00, 07:45, 08:30, 09:15, 10:00, 10:45, 11:30, 12:15, 14:00, 14:45, 15:30, 16:15, 17:00, 17:45, 18:30, 19:15";
        $sheet->setCellValue("A{$row}", $instrucciones);
        $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getRowDimension($row)->setRowHeight(110);
        $row += 2;
        
        // EJEMPLOS DE CÓDIGOS VÁLIDOS
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'EJEMPLOS DE CÓDIGOS VÁLIDOS:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$row}")->getFont()->getColor()->setARGB('FF059669');
        $row++;
        
        // Materias
        $sheet->setCellValue("A{$row}", 'Materias:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'MAT-101 (Cálculo I), MAT-102 (Álgebra), FIS-201 (Física I), QUI-301 (Química General)');
        $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);
        $row++;
        
        // Docentes
        $sheet->setCellValue("A{$row}", 'Docentes:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'DOC001, DOC002, DOC003, etc. O use AUTO para asignación automática');
        $row++;
        
        // Grupos
        $sheet->setCellValue("A{$row}", 'Grupos:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", '1, 2, 3, 4, etc.');
        $row++;
        
        // Aulas
        $sheet->setCellValue("A{$row}", 'Aulas:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'A-101, A-102, B-201, C-301, LAB-01, LAB-02');
        $row++;
        
        // Días
        $sheet->setCellValue("A{$row}", 'Días:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'Un día: Lunes | Múltiples días: Lunes, Miércoles, Viernes (separe con comas)');
        $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);
        $row++;
        
        // Horas
        $sheet->setCellValue("A{$row}", 'Horarios:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'Ejemplo: 07:00-08:30 (2 módulos), 07:45-09:15 (2 módulos), 09:15-11:45 (4 módulos)');
        $sheet->getStyle("B{$row}")->getAlignment()->setWrapText(true);
        $row++;
    }
}

