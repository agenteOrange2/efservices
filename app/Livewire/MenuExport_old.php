<?php

namespace App\Livewire;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class MenuExport extends Component
{
    public $exportExcel;
    public $exportPdf;

    public function downloadExcel()
    {
        return redirect()->route($this->exportExcelRoute);
    }

    public function downloadPdf()
    {
        return redirect()->route($this->exportPdfRoute);
    }

    public function render()
    {
        return view('livewire.menu-export');
    }
}