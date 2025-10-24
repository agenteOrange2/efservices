<?php

namespace App\Exports;

use App\Models\Carrier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CarriersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $carriers;
    protected $filters;

    public function __construct($carriers, $filters = [])
    {
        $this->carriers = $carriers;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->carriers;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nombre del Carrier',
            'Email',
            'Teléfono',
            'Usuario Asignado',
            'Email del Usuario',
            'Progreso (%)',
            'Documentos Aprobados',
            'Documentos Pendientes',
            'Total Documentos',
            'Estado',
            'Documentos Expirando',
            'Fecha de Registro',
            'Última Actualización',
        ];
    }

    /**
     * @param mixed $carrier
     * @return array
     */
    public function map($carrier): array
    {
        $userCarrier = $carrier->userCarriers->first();
        
        return [
            $carrier->id,
            $carrier->name,
            $carrier->email ?? 'Sin email',
            $carrier->phone ?? 'Sin teléfono',
            $userCarrier ? $userCarrier->user->name : 'Sin asignar',
            $userCarrier ? $userCarrier->user->email : 'Sin asignar',
            $carrier->completion_percentage . '%',
            $carrier->documents_summary['approved'] ?? 0,
            $carrier->documents_summary['pending'] ?? 0,
            $carrier->documents_summary['total'] ?? 0,
            $this->getStatusText($carrier->document_status),
            $carrier->expiring_documents ?? 0,
            $carrier->created_at->format('d/m/Y H:i'),
            $carrier->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Estilo para los encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Estilo para todas las celdas
            "A1:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Estilo para filas pares (zebra striping)
            "A2:{$lastColumn}{$lastRow}" => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA'],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $date = now()->format('d-m-Y');
        return "Carriers Export {$date}";
    }

    /**
     * Convertir estado a texto legible
     */
    private function getStatusText($status): string
    {
        switch ($status) {
            case 'active':
                return 'Activo';
            case 'pending':
                return 'Pendiente';
            case 'inactive':
                return 'Incompleto';
            default:
                return 'Desconocido';
        }
    }
}