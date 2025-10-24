<?php

namespace App\Exports;

use App\Models\Carrier;
use Barryvdh\DomPDF\Facade\Pdf;

class CarriersPdfExport
{
    protected $carriers;
    protected $filters;

    public function __construct($carriers, $filters = [])
    {
        $this->carriers = $carriers;
        $this->filters = $filters;
    }

    /**
     * Generar PDF de carriers
     */
    public function generate()
    {
        $data = [
            'carriers' => $this->carriers,
            'filters' => $this->filters,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'total_carriers' => $this->carriers->count(),
            'analytics' => $this->getAnalytics(),
        ];

        $pdf = Pdf::loadView('exports.carriers-pdf', $data);
        
        // Configurar el PDF
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);

        return $pdf;
    }

    /**
     * Obtener analytics para el PDF
     */
    private function getAnalytics()
    {
        $total = $this->carriers->count();
        $active = $this->carriers->where('document_status', 'active')->count();
        $pending = $this->carriers->where('document_status', 'pending')->count();
        $incomplete = $this->carriers->where('document_status', 'inactive')->count();

        return [
            'total_carriers' => $total,
            'active_carriers' => $active,
            'pending_carriers' => $pending,
            'incomplete_carriers' => $incomplete,
            'completion_rate' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Descargar PDF
     */
    public function download($filename = null)
    {
        $filename = $filename ?: 'carriers-export-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        return $this->generate()->download($filename);
    }

    /**
     * Mostrar PDF en el navegador
     */
    public function stream($filename = null)
    {
        $filename = $filename ?: 'carriers-export-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        return $this->generate()->stream($filename);
    }
}