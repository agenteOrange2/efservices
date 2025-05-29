<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;
use App\Helpers\Constants;
use App\Models\Membership;
use Illuminate\Support\Str;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\CarrierDocument;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CarrierDocumentService;
use App\Traits\SendsCustomNotifications;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\Carrier\NewCarrierNotification;

class CarrierController extends Controller
{

    use SendsCustomNotifications;
    protected $documentService;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

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
            'dot_number' => 'nullable|string|max:255',
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

        // Validar límites antes de crear
        /*
        $membership = Membership::findOrFail($request->id_plan);
        if (!$membership->canAddCarriers()) {
            return back()->with('error', 'Membership limit reached');
        }
        */

        // Generar documentos base automáticamente
        $this->documentService->generateBaseDocuments($carrier);

        // Subir logo (si se envió)
        if ($request->hasFile('logo_carrier')) {
            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName(Str::slug($carrier->name) . '.webp')
                ->toMediaCollection('logo_carrier');
        }

        // Notificar al admin sobre el nuevo carrier
        try {
            // Obtener todos los superadmins
            $superadmins = User::role('superadmin')->get();
            
            foreach ($superadmins as $admin) {
                $admin->notify(new NewCarrierNotification($carrier));
            }
    
            Log::info('New carrier notification sent to all superadmins', [
                'carrier_id' => $carrier->id,
                'superadmin_count' => $superadmins->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending new carrier notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'carrier_id' => $carrier->id
            ]);
        }

        // Redirigir al tab de usuarios del carrier
        return redirect()
            ->route('admin.carrier.user_carriers.index', $carrier)
            ->with($this->sendNotification(
                'success',
                'Carrier creado exitosamente. Ahora puedes administrar los usuarios asociados.'
            ));
    }

    /**
     * Generar documentos base para el carrier basado en los tipos predefinidos.
     */
    private function generateBaseDocuments(Carrier $carrier)
    {
        $documentTypes = DocumentType::all();

        foreach ($documentTypes as $type) {
            // Crear el CarrierDocument si no existe
            $carrierDocument = CarrierDocument::firstOrCreate([
                'carrier_id' => $carrier->id,
                'document_type_id' => $type->id,
            ], [
                'status' => CarrierDocument::STATUS_PENDING,
                'date' => now(),
            ]);

            // Verificar si el DocumentType tiene un archivo predeterminado
            $defaultMedia = $type->getFirstMedia('default_documents');

            // NO copiar el archivo predeterminado; se usa la referencia desde 'default_documents'.
            if ($defaultMedia && !$carrierDocument->getFirstMedia('carrier_documents')) {
                // Simplemente registramos que este documento tiene un predeterminado.
                $carrierDocument->update(['status' => CarrierDocument::STATUS_PENDING]);
            }
        }
    }


    public function documents(Carrier $carrier)
    {
        $documents = CarrierDocument::where('carrier_id', $carrier->id)->with('documentType')->get();
        $documentTypes = DocumentType::all(); // Aquí cargamos los tipos de documentos

        return view('admin.carrier.documents', compact('carrier', 'documents', 'documentTypes'));
    }
    
    /**
     * Actualizar el estado de un documento de carrier.
     */
    public function updateDocumentStatus(Request $request, CarrierDocument $document)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);
        
        $document->update([
            'status' => $validated['status'],
        ]);
        
        return redirect()
            ->back()
            ->with($this->sendNotification(
                'success',
                'Document status updated successfully.'
            ));
    }
    
    /**
     * Eliminar la foto de perfil del carrier.
     */
    public function deletePhoto(Carrier $carrier)
    {
        try {
            // Eliminar la foto actual
            if ($carrier->hasMedia('logo_carrier')) {
                $carrier->getFirstMedia('logo_carrier')->delete();
            }
            
            return response()->json([
                'success' => true,
                'defaultPhotoUrl' => asset('images/default-carrier-logo.png')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting carrier photo', [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar el formulario para editar un carrier.
     */
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();
        $usStates = Constants::usStates();

        
        // Generar URL de referencia con el prefijo correcto
        $referralUrl = url("/driver/register/{$carrier->slug}?token={$carrier->referrer_token}");
        
        return view('admin.carrier.edit', compact('carrier', 'memberships', 'usStates','referralUrl'));
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
            'dot_number' => 'nullable|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_img' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|integer|in:0,1,2',
            'referrer_token' => 'nullable|string|max:16|unique:carriers,referrer_token,' . $carrier->id,
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
            ->with($this->sendNotification(
                'success',
                'Carrier actualizado exitosamente.',
                'Los cambios han sido guardados correctamente.'
            ));
    }

    public function approveDefaultDocument(Request $request, Carrier $carrier, CarrierDocument $document)
    {
        $validated = $request->validate(['approved' => 'required|boolean']);

        $document->update([
            'status' => $validated['approved'] ? CarrierDocument::STATUS_APPROVED : CarrierDocument::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => $validated['approved'] ? 'Default document approved' : 'Default document unapproved',
        ]);
    }

    /**
     * Eliminar un carrier.
     */
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()
            ->route('admin.carriers.index')
            ->with($this->sendNotification(
                'error',
                'Carrier eliminado exitosamente.',
                'El carrier y todos sus datos asociados han sido eliminados.'
            ));
    }
}
