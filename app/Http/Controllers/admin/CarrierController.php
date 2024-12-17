<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Models\Carrier;
use App\Models\Membership;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class CarrierController extends Controller
{
    /**
     * Mostrar todos los carriers.
     */
    public function index()
    {
        $carriers = Carrier::with('membership')->paginate(10);
        return view('admin.carrier.index', compact('carriers'));
    }

    /**
     * Mostrar el formulario para crear un nuevo carrier.
     */
    public function create()
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();
        $usStates = Constants::usStates();
        return view('admin.carrier.create', compact('memberships', 'usStates'));
    }

    /**
     * Guardar un nuevo carrier y asignar documentos base.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_img' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|integer|in:0,1,2',
        ]);
    
        // Crear el carrier
        $carrier = Carrier::create(array_merge($validated, [
            'slug' => Str::slug($validated['name']),
            'referrer_token' => Str::random(16),
        ]));
    
        // Generar documentos base automáticamente
        $this->generateBaseDocuments($carrier);
    
        // Subir logo (si se envió)
        if ($request->hasFile('logo_carrier')) {
            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName(Str::slug($carrier->name) . '.webp')
                ->toMediaCollection('logo_carrier');
        }
    
        // Redirigir al tab de usuarios del carrier
        return redirect()->route('admin.carrier.user_carriers.index', $carrier)
        ->with('success', 'Carrier creado exitosamente. Ahora puedes administrar los usuarios asociados.');
    
    }
    
    /**
     * Generar documentos base para el carrier basado en los tipos predefinidos.
     */
    private function generateBaseDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();
    
        foreach ($documentTypes as $type) {
            CarrierDocument::create([
                'carrier_id' => $carrier->id,
                'document_type_id' => $type->id,
                'filename' => 'placeholder.pdf', // Asignar un valor predeterminado
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]);
        }
    }

    public function documents(Carrier $carrier)
{
    // Obtener documentos relacionados al Carrier
    $documents = CarrierDocument::where('carrier_id', $carrier->id)
        ->with('documentType') // Incluye el tipo de documento
        ->get();

    // Renderiza la vista con los datos
    return view('admin.carrier.documents.index', compact('carrier', 'documents'));
}

    

    /**
     * Mostrar el formulario para editar un carrier.
     */
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();
        $usStates = Constants::usStates();
        return view('admin.carrier.edit', compact('carrier', 'memberships', 'usStates'));
    }

    /**
     * Actualizar un carrier existente.
     */
    public function update(Request $request, Carrier $carrier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_img' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|integer|in:0,1,2',
        ]);

        // Actualizar slug solo si cambia el nombre
        if ($carrier->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Subir logo (si se envió)
        if ($request->hasFile('logo_carrier')) {
            $fileName = strtolower(str_replace(' ', '_', $carrier->name)) . '.webp';

            // Limpiar la colección anterior
            $carrier->clearMediaCollection('logo_carrier');

            // Guardar la nueva foto con el nombre personalizado
            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName($fileName)
                ->toMediaCollection('logo_carrier');
        }

        $carrier->update($validated);

        return redirect()
            ->route('admin.carrier.user_carriers.index', $carrier)
            ->with('success', 'Carrier actualizado exitosamente.');
    }

    /**
     * Eliminar un carrier.
     */
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier eliminado exitosamente.');
    }
}
