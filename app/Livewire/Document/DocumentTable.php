<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentTable extends Component
{
    use WithPagination;

    public $search = ''; // Campo de búsqueda mejorado
    public $filters = [
        'status' => null, // Estado del carrier: active, pending, incomplete
        'date_range' => ['start' => null, 'end' => null], // Rango de fechas
        'completion_range' => ['min' => null, 'max' => null], // Rango de completitud
        'document_types' => [], // Tipos de documentos específicos
        'expiring_soon' => false, // Documentos que expiran pronto
    ];
    public $perPage = 10; // Resultados por página
    public $sortField = 'id'; // Campo de ordenamiento
    public $sortDirection = 'asc'; // Dirección del ordenamiento
    public $openPopover = false; // Estado del popover de filtros
    public $loading = false; // Estado de carga
    public $exportFormat = 'excel'; // Formato de exportación

    protected $listeners = [
        'filtersUpdated', 
        'updateDateRange',
        'exportData',
        'refreshTable' => '$refresh'
    ];

    protected $rules = [
        'search' => 'nullable|string|max:255',
        'filters.status' => 'nullable|in:active,pending,incomplete',
        'filters.date_range.start' => 'nullable|date',
        'filters.date_range.end' => 'nullable|date|after_or_equal:filters.date_range.start',
        'filters.completion_range.min' => 'nullable|numeric|min:0|max:100',
        'filters.completion_range.max' => 'nullable|numeric|min:0|max:100|gte:filters.completion_range.min',
        'perPage' => 'integer|in:5,10,25,50,100',
        'sortField' => 'string|in:id,name,email,created_at,completion_percentage',
        'sortDirection' => 'in:asc,desc',
        'exportFormat' => 'in:excel,pdf'
    ];

    public function mount()
    {
        $this->perPage = 10;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
    }

    public function updating($property, $value)
    {
        // Validar entrada en tiempo real
        if ($property === 'search') {
            $this->validateOnly('search');
        }
        
        if (in_array($property, ['search', 'filters', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function updated($property)
    {
        if (str_starts_with($property, 'filters.')) {
            $this->validateOnly($property);
        }
    }

    public function updateDateRange($dates)
    {
        $this->validate([
            'filters.date_range.start' => 'nullable|date',
            'filters.date_range.end' => 'nullable|date|after_or_equal:filters.date_range.start',
        ]);

        $this->filters['date_range']['start'] = $dates['start'];
        $this->filters['date_range']['end'] = $dates['end'];
        $this->dispatch('filtersUpdated', $this->filters);
        $this->resetPage();
    }

    public function applyFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filters = [
            'status' => null,
            'date_range' => ['start' => null, 'end' => null],
            'completion_range' => ['min' => null, 'max' => null],
            'document_types' => [],
            'expiring_soon' => false,
        ];
        $this->reset(['search']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        $this->validate(['sortField' => 'string|in:id,name,email,created_at,completion_percentage']);
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function togglePopover()
    {
        $this->openPopover = !$this->openPopover;
    }

    public function setLoading($state = true)
    {
        $this->loading = $state;
    }

    public function exportData($format = 'excel')
    {
        $this->validate(['exportFormat' => 'in:excel,pdf']);
        
        $this->setLoading(true);
        
        try {
            $carriers = $this->getCarriersQuery()->get();
            
            // Calcular datos necesarios para la exportación
            $totalDocuments = Cache::remember('total_document_types', 3600, function () {
                return DocumentType::count();
            });

            $carriers->transform(function ($carrier) use ($totalDocuments) {
                $approvedDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_APPROVED)->count();
                $pendingDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_PENDING)->count();
                $rejectedDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_REJECTED)->count();

                $carrier->completion_percentage = $totalDocuments > 0
                    ? round(($approvedDocuments / $totalDocuments) * 100, 1)
                    : 0;

                $carrier->document_status = $approvedDocuments === $totalDocuments ? 'active' : 
                                          ($pendingDocuments > 0 ? 'pending' : 'incomplete');

                $carrier->documents_summary = [
                    'approved' => $approvedDocuments,
                    'pending' => $pendingDocuments,
                    'rejected' => $rejectedDocuments,
                    'total' => $totalDocuments,
                ];

                return $carrier;
            });
            
            if ($format === 'excel') {
                return $this->exportToExcel($carriers);
            } else {
                return $this->exportToPdf($carriers);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar: ' . $e->getMessage());
        } finally {
            $this->setLoading(false);
        }
    }

    private function exportToExcel($carriers)
    {
        // Crear el contenido CSV para Excel
        $filename = 'carriers-documents-' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/public/' . $filename);
        
        // Crear directorio si no existe
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Encabezados
        fputcsv($file, [
            'Carrier Name',
            'User Carrier',
            'User Email',
            'Completion %',
            'Approved Docs',
            'Pending Docs',
            'Rejected Docs',
            'Total Docs',
            'Status',
            'Created At'
        ]);
        
        // Datos
        foreach ($carriers as $carrier) {
            $userCarrier = $carrier->userCarriers->first();
            fputcsv($file, [
                $carrier->name,
                $userCarrier ? $userCarrier->user->name : 'N/A',
                $userCarrier ? $userCarrier->user->email : 'N/A',
                $carrier->completion_percentage . '%',
                $carrier->documents_summary['approved'],
                $carrier->documents_summary['pending'],
                $carrier->documents_summary['rejected'],
                $carrier->documents_summary['total'],
                ucfirst($carrier->document_status),
                $carrier->created_at->format('Y-m-d H:i:s')
            ]);
        }
        
        fclose($file);
        
        session()->flash('success', 'Exportación a Excel completada');
        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    private function exportToPdf($carriers)
    {
        // Crear contenido HTML para PDF con estilos optimizados para DomPDF
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: DejaVu Sans, sans-serif; 
                    font-size: 10px; 
                    margin: 20px;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 2px solid #333;
                    padding-bottom: 15px;
                }
                .header h1 { 
                    font-size: 18px; 
                    margin: 0 0 10px 0; 
                    color: #2c3e50;
                }
                .header p { 
                    font-size: 12px; 
                    margin: 0; 
                    color: #7f8c8d;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 20px; 
                }
                th { 
                    background-color: #34495e; 
                    color: white; 
                    padding: 8px 6px; 
                    text-align: left; 
                    font-weight: bold;
                    font-size: 9px;
                    border: 1px solid #2c3e50;
                }
                td { 
                    border: 1px solid #bdc3c7; 
                    padding: 6px; 
                    text-align: left; 
                    font-size: 8px;
                    vertical-align: top;
                }
                tr:nth-child(even) { 
                    background-color: #f8f9fa; 
                }
                .status-active { 
                    color: #27ae60; 
                    font-weight: bold; 
                }
                .status-pending { 
                    color: #f39c12; 
                    font-weight: bold; 
                }
                .status-incomplete { 
                    color: #e74c3c; 
                    font-weight: bold; 
                }
                .completion-bar {
                    width: 50px;
                    height: 8px;
                    background-color: #ecf0f1;
                    border-radius: 4px;
                    overflow: hidden;
                    display: inline-block;
                    vertical-align: middle;
                }
                .completion-fill {
                    height: 100%;
                    background-color: #27ae60;
                    border-radius: 4px;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 8px;
                    color: #7f8c8d;
                    border-top: 1px solid #bdc3c7;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Carriers Documents Report</h1>
                <p>Generated on: ' . now()->format('d/m/Y H:i:s') . '</p>
                <p>Total Carriers: ' . $carriers->count() . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Carrier</th>
                        <th style="width: 20%;">User Assign</th>
                        <th style="width: 20%;">Email User</th>
                        <th style="width: 15%;">Complete</th>
                        <th style="width: 8%;">Approved</th>
                        <th style="width: 8%;">Pending</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;">Register Date</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($carriers as $carrier) {
            $userCarrier = $carrier->userCarriers->first();
            $statusClass = 'status-' . $carrier->document_status;
            
            // Calcular el ancho de la barra de progreso
            $progressWidth = $carrier->completion_percentage;
            
            $html .= '<tr>';
            $html .= '<td><strong>' . htmlspecialchars($carrier->name) . '</strong></td>';
            $html .= '<td>' . ($userCarrier ? htmlspecialchars($userCarrier->user->name) : 'Unassigned') . '</td>';
            $html .= '<td>' . ($userCarrier ? htmlspecialchars($userCarrier->user->email) : 'N/A') . '</td>';
            $html .= '<td>
                        <div style="display: flex; align-items: center;">
                            <span style="margin-right: 5px;">' . $carrier->completion_percentage . '%</span>
                            <div class="completion-bar">
                                <div class="completion-fill" style="width: ' . $progressWidth . '%;"></div>
                            </div>
                        </div>
                      </td>';
            $html .= '<td style="text-align: center;"><strong>' . $carrier->documents_summary['approved'] . '</strong></td>';
            $html .= '<td style="text-align: center;"><strong>' . $carrier->documents_summary['pending'] . '</strong></td>';
            $html .= '<td class="' . $statusClass . '">' . ucfirst($carrier->document_status) . '</td>';
            $html .= '<td>' . $carrier->created_at->format('d/m/Y') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
            </table>
            
            <div class="footer">
                <p>Report generated automatically by EF Services</p>
                <p>Page 1 of 1 - Total records: ' . $carriers->count() . '</p>
            </div>
        </body>
        </html>';
        
        // Generar PDF usando DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape'); // Orientación horizontal para mejor visualización de la tabla
        
        $filename = 'carriers-documents-' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        session()->flash('success', 'Carriers documents report generated successfully');
        
        // Retornar el PDF para descarga
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function viewCarrierDocuments($carrierId)
    {
        return redirect()->route('carrier.documents', $carrierId);
    }

    private function getCarriersQuery()
    {
        $totalDocuments = Cache::remember('total_document_types', 3600, function () {
            return DocumentType::count();
        });

        // Optimización con eager loading mejorado
        $query = Carrier::with([
            'documents' => function ($query) {
                $query->select('id', 'carrier_id', 'document_type_id', 'status', 'created_at');
            },
            'documents.documentType:id,name',
            'userCarriers' => function ($query) {
                $query->select('id', 'carrier_id', 'user_id');
            },
            'userCarriers.user:id,name,email'
        ]);

        // Búsqueda mejorada en múltiples campos
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('address', 'like', $searchTerm)
                  ->orWhere('ein_number', 'like', $searchTerm)
                  ->orWhere('dot_number', 'like', $searchTerm)
                  ->orWhere('mc_number', 'like', $searchTerm)
                  ->orWhere('state', 'like', $searchTerm)
                  ->orWhere('zipcode', 'like', $searchTerm)
                  ->orWhereHas('userCarriers.user', function ($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', $searchTerm)
                               ->orWhere('email', 'like', $searchTerm);
                  });
            });
        }

        // Filtro de estado mejorado
        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] === 'active') {
                $query->whereHas('documents', function ($subQuery) use ($totalDocuments) {
                    $subQuery->selectRaw('carrier_id, COUNT(*) as approved_count')
                            ->where('status', CarrierDocument::STATUS_APPROVED)
                            ->groupBy('carrier_id')
                            ->havingRaw('approved_count = ?', [$totalDocuments]);
                });
            } elseif ($this->filters['status'] === 'pending') {
                $query->whereHas('documents', function ($subQuery) {
                    $subQuery->where('status', CarrierDocument::STATUS_PENDING);
                });
            } elseif ($this->filters['status'] === 'incomplete') {
                $query->whereDoesntHave('documents', function ($subQuery) use ($totalDocuments) {
                    $subQuery->selectRaw('carrier_id, COUNT(*) as approved_count')
                            ->where('status', CarrierDocument::STATUS_APPROVED)
                            ->groupBy('carrier_id')
                            ->havingRaw('approved_count = ?', [$totalDocuments]);
                });
            }
        }

        // Filtro de rango de fechas
        if (!empty($this->filters['date_range']['start']) && !empty($this->filters['date_range']['end'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->filters['date_range']['start'])->startOfDay(),
                Carbon::parse($this->filters['date_range']['end'])->endOfDay(),
            ]);
        }

        // Filtro de tipos de documentos específicos
        if (!empty($this->filters['document_types'])) {
            $query->whereHas('documents', function ($subQuery) {
                $subQuery->whereIn('document_type_id', $this->filters['document_types']);
            });
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getCarriersQuery();

        // Ordenamiento
        if ($this->sortField === 'completion_percentage') {
            // Ordenamiento especial para porcentaje de completitud
            $totalDocuments = Cache::remember('total_document_types', 3600, function () {
                return DocumentType::count();
            });
            
            $query->withCount([
                'documents as approved_documents_count' => function ($query) {
                    $query->where('status', CarrierDocument::STATUS_APPROVED);
                }
            ])->orderByRaw(
                "CASE WHEN ? > 0 THEN (approved_documents_count / ?) * 100 ELSE 0 END {$this->sortDirection}",
                [$totalDocuments, $totalDocuments]
            );
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        // Paginación
        $carriers = $query->paginate($this->perPage);

        // Cálculo optimizado de progreso y transformación de datos
        $totalDocuments = Cache::remember('total_document_types', 3600, function () {
            return DocumentType::count();
        });

        $carriers->getCollection()->transform(function ($carrier) use ($totalDocuments) {
            $approvedDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_APPROVED)->count();
            $pendingDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_PENDING)->count();
            $rejectedDocuments = $carrier->documents->where('status', CarrierDocument::STATUS_REJECTED)->count();

            $carrier->completion_percentage = $totalDocuments > 0
                ? round(($approvedDocuments / $totalDocuments) * 100, 1)
                : 0;

            $carrier->document_status = $approvedDocuments === $totalDocuments ? 'active' : 
                                      ($pendingDocuments > 0 ? 'pending' : 'incomplete');

            $carrier->documents_summary = [
                'approved' => $approvedDocuments,
                'pending' => $pendingDocuments,
                'rejected' => $rejectedDocuments,
                'total' => $totalDocuments,
                'missing' => $totalDocuments - ($approvedDocuments + $pendingDocuments + $rejectedDocuments)
            ];

            return $carrier;
        });

        // Obtener analytics con caché
        $analytics = $this->getAnalytics();

        return view('livewire.document.document-table', [
            'carriers' => $carriers,
            'analytics' => $analytics,
            'documentTypes' => DocumentType::select('id', 'name')->get(),
        ]);
    }

    private function getAnalytics()
    {
        return Cache::remember('carriers_analytics', 300, function () {
            $totalCarriers = Carrier::count();
            $activeCarriers = Carrier::whereHas('documents', function ($query) {
                $totalDocs = DocumentType::count();
                $query->selectRaw('carrier_id, COUNT(*) as approved_count')
                      ->where('status', CarrierDocument::STATUS_APPROVED)
                      ->groupBy('carrier_id')
                      ->havingRaw('approved_count = ?', [$totalDocs]);
            })->count();

            $pendingCarriers = Carrier::whereHas('documents', function ($query) {
                $query->where('status', CarrierDocument::STATUS_PENDING);
            })->count();

            return [
                'total_carriers' => $totalCarriers,
                'active_carriers' => $activeCarriers,
                'pending_carriers' => $pendingCarriers,
                'completion_rate' => $totalCarriers > 0 ? round(($activeCarriers / $totalCarriers) * 100, 1) : 0,
            ];
        });
    }
}
