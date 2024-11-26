<?php

namespace App\Livewire;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class MenuExport extends Component
{
    public $exportExcel = false;
    public $exportPdf = false;

    public function render()
    {
        return view('livewire.menu-export');
    }
}
