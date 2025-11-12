<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class AsistenciasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $asistencias;
    protected $titulo;

    public function __construct($asistencias, $titulo = 'Reporte de Asistencias')
    {
        $this->asistencias = collect($asistencias);
        $this->titulo = $titulo;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->asistencias;
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'Fecha',
            'Docente',
            'Código',
            'Materia',
            'Grupo',
            'Horario',
            'Aula',
            'Estado',
            'Registrado Por',
            'Fecha de Registro',
            'Observación',
        ];
    }

    /**
     * Mapeo de datos para cada fila
     */
    public function map($asistencia): array
    {
        $docente = $asistencia->docente;
        $asignacion = $asistencia->asignacion;
        $grupoMateria = $asignacion->grupo_materia ?? null;
        $horario = $asignacion->horario ?? null;
        $hora = $horario->hora ?? null;

        return [
            Carbon::parse($asistencia->fecha)->format('d/m/Y'),
            $docente ? "{$docente->nombre} {$docente->apellidos}" : 'N/A',
            $docente->codigo ?? 'N/A',
            $grupoMateria->materia->nombre ?? 'N/A',
            $grupoMateria ? "Grupo {$grupoMateria->grupo->numero}" : 'N/A',
            $hora ? "{$hora->hora_inicio} - {$hora->hora_fin}" : 'N/A',
            $asignacion->aula->nombre ?? 'N/A',
            $asistencia->estado,
            $asistencia->registrada_por_docente ? 'Docente' : 'Sistema',
            $asistencia->fecha_asistencia ? Carbon::parse($asistencia->fecha_asistencia)->format('d/m/Y H:i') : '-',
            $asistencia->observacion ?? '-',
        ];
    }

    /**
     * Estilos para la hoja
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return substr($this->titulo, 0, 31); // Excel limita a 31 caracteres
    }
}
