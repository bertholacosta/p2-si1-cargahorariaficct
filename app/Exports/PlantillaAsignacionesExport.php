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
        // Encabezados en la primera fila
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF6366F1');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Ejemplos con color diferente (filas 2-4)
        $sheet->getStyle('A2:G4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
        
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
}

