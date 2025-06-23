<?php

namespace App\Livewire;

use Livewire\Component;

class MenuExport extends Component
{
    public $exportExcel = false;
    public $exportPdf = false;
    public $exportExcelRoute = null;
    public $exportPdfRoute = null;
    public $filterParams = [];

    protected $listeners = [
        'exportToExcel' => 'exportToExcel',
        'exportToPdf' => 'exportToPdf',
        'updateExportFilters' => 'updateFilterParams'
    ];

    public function mount($exportExcel = false, $exportPdf = false, $exportExcelRoute = null, $exportPdfRoute = null)
    {
        $this->exportExcel = $exportExcel;
        $this->exportPdf = $exportPdf;
        $this->exportExcelRoute = $exportExcelRoute;
        $this->exportPdfRoute = $exportPdfRoute;
    }
    
    public function updateFilterParams($params)
    {
        logger()->info('MenuExport recibió parámetros de filtro:', $params);
        
        // Asegurarse de que $params tenga la estructura esperada
        if (isset($params['filters'])) {
            $this->filterParams = $params['filters'];
        } else {
            $this->filterParams = [];
        }
        
        // Guardar el término de búsqueda por separado
        if (isset($params['search'])) {
            $this->filterParams['search'] = $params['search'];
        }
        
        logger()->debug('FilterParams actualizados en MenuExport:', $this->filterParams);
    }

    public function exportToExcel()
    {
        logger()->info('Ejecutando exportToExcel en MenuExport');
        if ($this->exportExcelRoute) {
            $queryParams = [];
            $search = null;
            
            if (isset($this->filterParams['search'])) {
                $search = $this->filterParams['search'];
                $filtersCopy = $this->filterParams;
                unset($filtersCopy['search']);
                if (!empty($filtersCopy)) {
                    $queryParams['filters'] = json_encode($filtersCopy);
                }
            } else if (!empty($this->filterParams)) {
                $queryParams['filters'] = json_encode($this->filterParams);
            }
            
            if ($search) {
                $queryParams['search'] = $search;
            }
            
            logger()->info('Generando URL de exportación a Excel con parámetros:', [
                'ruta' => $this->exportExcelRoute,
                'params' => $queryParams
            ]);
            
            // Generar la URL de exportación sin redireccionar
            $url = route($this->exportExcelRoute, $queryParams);
            
            // Usar JavaScript para abrir en una nueva pestaña o forzar descarga
            $this->dispatch('downloadExport', ['url' => $url]);
            
            // Notificar al usuario
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Generating Excel export',
                'details' => 'Your file will download automatically.'
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No Excel export route configured'
            ]);
        }
    }

    public function exportToPdf()
    {
        logger()->info('Ejecutando exportToPdf en MenuExport');
        if ($this->exportPdfRoute) {
            $queryParams = [];
            $search = null;
            
            if (isset($this->filterParams['search'])) {
                $search = $this->filterParams['search'];
                $filtersCopy = $this->filterParams;
                unset($filtersCopy['search']);
                if (!empty($filtersCopy)) {
                    $queryParams['filters'] = json_encode($filtersCopy);
                }
            } else if (!empty($this->filterParams)) {
                $queryParams['filters'] = json_encode($this->filterParams);
            }
            
            if ($search) {
                $queryParams['search'] = $search;
            }
            
            logger()->info('Generando URL de exportación a PDF con parámetros:', [
                'ruta' => $this->exportPdfRoute,
                'params' => $queryParams
            ]);
            
            // Generar la URL de exportación sin redireccionar
            $url = route($this->exportPdfRoute, $queryParams);
            
            // Usar JavaScript para abrir en una nueva pestaña o forzar descarga
            $this->dispatch('downloadExport', ['url' => $url]);
            
            // Notificar al usuario
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Generating PDF export',
                'details' => 'Your file will download automatically.'
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No PDF export route configured'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.menu-export');
    }
}
