<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AsistenciasHorariosExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    ShouldAutoSize,
    WithTitle
{
    protected $asistencias;
    protected $titulo;

    public function __construct($asistencias, $titulo = 'Reporte de Horarios')
    {
        $this->asistencias = $asistencias;
        $this->titulo = $titulo;
    }

    /**
     * Retorna la colección de datos
     */
    public function collection()
    {
        return $this->asistencias;
    }

    /**
     * Define los encabezados
     */
    public function headings(): array
    {
        return [
            'FECHA',
            'DÍA',
            'HORA INICIO',
            'HORA FIN',
            'DOCENTE',
            'CÓDIGO DOCENTE',
            'MATERIA',
            'GRUPO',
            'AULA',
            'ESTADO',
            'HORA REGISTRO',
            'REGISTRADO POR',
            'GESTIÓN',
            'JUSTIFICACIÓN',
            'OBSERVACIÓN',
        ];
    }

    /**
     * Mapea los datos de cada fila
     */
    public function map($asistencia): array
    {
        $docente = $asistencia->docente;
        $asignacion = $asistencia->asignacion;
        $grupoMateria = $asignacion->grupoMateria ?? null;
        $horario = $asignacion->horario ?? null;
        $hora = $horario->hora ?? null;
        $dia = $horario->dia ?? null;
        $aula = $asignacion->aula ?? null;
        $gestion = $asignacion->gestion ?? null;
        $justificacion = $asistencia->justificacion;

        return [
            $asistencia->fecha->format('d/m/Y'),
            $dia->nombre ?? 'N/A',
            $hora->hora_inicio ?? 'N/A',
            $hora->hora_fin ?? 'N/A',
            $docente ? "{$docente->nombre} {$docente->apellidos}" : 'N/A',
            $asistencia->id_docente ?? 'N/A',
            $grupoMateria->materia->nombre ?? 'N/A',
            $grupoMateria->grupo->nombre ?? 'N/A',
            $aula->nombre ?? 'N/A',
            $this->obtenerEstadoFormateado($asistencia->estado),
            $asistencia->fecha_asistencia ? $asistencia->fecha_asistencia->format('d/m/Y H:i:s') : 'N/A',
            $asistencia->registrada_por_docente ? 'Docente' : 'Sistema/Admin',
            $gestion ? $gestion->nombre_completo : 'N/A',
            $justificacion ? 'Sí' : 'No',
            $asistencia->observacion ?? '',
        ];
    }

    /**
     * Formatear el estado para mejor visualización
     */
    private function obtenerEstadoFormateado($estado): string
    {
        $estados = [
            'Presente' => '✓ Presente',
            'Falta' => '✗ Falta',
            'Retraso' => '⚠ Retraso',
            'Justificada' => '✓ Justificada',
            'Licencia' => 'Licencia',
        ];

        return $estados[$estado] ?? $estado;
    }

    /**
     * Aplicar estilos al archivo Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo del encabezado
        $sheet->getStyle('A1:O1')->applyFromArray([
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
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Altura de la fila de encabezado
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Aplicar bordes a todas las celdas con datos
        $ultimaFila = $sheet->getHighestRow();
        $sheet->getStyle("A1:O{$ultimaFila}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Alineación centrada para columnas específicas
        $sheet->getStyle("A2:A{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:D{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("J2:J{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("L2:N{$ultimaFila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Colores condicionales para el estado
        foreach (range(2, $ultimaFila) as $row) {
            $estadoCelda = $sheet->getCell("J{$row}")->getValue();
            
            if (strpos($estadoCelda, 'Presente') !== false || strpos($estadoCelda, 'Justificada') !== false) {
                // Verde para presente y justificada
                $sheet->getStyle("J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C6EFCE']
                    ],
                    'font' => [
                        'color' => ['rgb' => '006100'],
                        'bold' => true
                    ]
                ]);
            } elseif (strpos($estadoCelda, 'Falta') !== false) {
                // Rojo para falta
                $sheet->getStyle("J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC7CE']
                    ],
                    'font' => [
                        'color' => ['rgb' => '9C0006'],
                        'bold' => true
                    ]
                ]);
            } elseif (strpos($estadoCelda, 'Retraso') !== false) {
                // Amarillo para retraso
                $sheet->getStyle("J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFEB9C']
                    ],
                    'font' => [
                        'color' => ['rgb' => '9C5700'],
                        'bold' => true
                    ]
                ]);
            } elseif (strpos($estadoCelda, 'Licencia') !== false) {
                // Azul para licencia
                $sheet->getStyle("J{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'BDD7EE']
                    ],
                    'font' => [
                        'color' => ['rgb' => '004085'],
                        'bold' => true
                    ]
                ]);
            }
        }

        return [];
    }

    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return substr($this->titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
