<?php

namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Storage;

class FMCSAClearinghouseStep extends Component
{
    // Propiedades
    public $driverId;
    public $pdfUrl;

    // Inicialización
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        // URL del PDF de instrucciones (asegúrate de que este archivo exista en tu storage)
        $this->pdfUrl = asset('storage/documents/Drivers_Responding_to_DACH_Consent_Requests_FMCSA_01.20.pdf');
    }

    // Método para finalizar y redireccionar (botón Finish)
    public function finish()
    {
        // Redireccionar a la página de índice
        $userDriverDetail = UserDriverDetail::with('carrier')->find($this->driverId);
        if ($userDriverDetail && $userDriverDetail->carrier) {
            $carrierSlug = $userDriverDetail->carrier->slug;
            return redirect()->route('admin.carrier.user_drivers.index', ['carrier' => $carrierSlug])
                ->with('success', 'La solicitud ha sido enviada para revisión.');
        }

        $carrierSlug = request()->route('carrier');
        return redirect()->route('admin.carrier.user_drivers.index', ['carrier' => $carrierSlug])
            ->with('success', 'La solicitud ha sido enviada para revisión.');
    }

    // Redireccionar a la página de Clearinghouse
    public function visitClearinghouse()
    {
        // Este método se utiliza principalmente para tracking (puedes registrar la visita si lo deseas)
        return redirect()->away('https://clearinghouse.fmcsa.dot.gov/');
    }

    // Ir al paso anterior
    public function previous()
    {
        $this->dispatch('prevStep');
    }

    // Guardar y salir
    public function saveAndExit()
    {
        $this->dispatch('saveAndExit');
    }

    public function render()
    {
        return view('livewire.driver.steps.f-m-c-s-a-clearinghouse-step');
    }
}
