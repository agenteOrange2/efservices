<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverAccident;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AccidentsController extends Controller
{
    // Vista para todos los accidentes
    public function index(Request $request)
    {
        $query = DriverAccident::query()
            ->with(['userDriverDetail.user', 'userDriverDetail.carrier']);

        // Aplicar filtros
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
        }

        if ($request->filled('driver_filter')) {
            $query->where('user_driver_detail_id', $request->driver_filter);
        }

        if ($request->filled('carrier_filter')) {
            $query->whereHas('userDriverDetail', function ($subq) use ($request) {
                $subq->where('carrier_id', $request->carrier_filter);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('accident_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('accident_date', '<=', $request->date_to);
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);
        $drivers = UserDriverDetail::with('user')->get();
        $carriers = Carrier::where('status', 1)->get();

        return view('admin.drivers.accidents.index', compact('accidents', 'drivers', 'carriers'));
    }

    // Vista para el historial de accidentes de un conductor específico
    public function driverHistory(UserDriverDetail $driver, Request $request)
    {
        $query = DriverAccident::where('user_driver_detail_id', $driver->id);

        // Aplicar filtros si existen
        if ($request->filled('search_term')) {
            $query->where('nature_of_accident', 'like', '%' . $request->search_term . '%')
                ->orWhere('comments', 'like', '%' . $request->search_term . '%');
        }

        // Ordenar resultados
        $sortField = $request->get('sort_field', 'accident_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $accidents = $query->paginate(10);

        return view('admin.drivers.accidents.driver_history', compact('driver', 'accidents'));
    }

    // Método para almacenar un nuevo accidente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        // Convertir checkboxes a valores booleanos
        $validated['had_injuries'] = isset($request->had_injuries);
        $validated['had_fatalities'] = isset($request->had_fatalities);

        // Solo incluir el número de lesiones/fatalidades si se marcó el checkbox
        if (!$validated['had_injuries']) {
            $validated['number_of_injuries'] = null;
        }
        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = null;
        }

        // Crear el registro de accidente
        $accident = DriverAccident::create($validated);

        // Procesar los archivos subidos
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    // Subir el archivo al accidente
                    $media = $accident->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('accident_documents');
                    
                    Log::info('Archivo de accidente subido correctamente', [
                        'accident_id' => $accident->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'collection' => 'accident_documents'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                        'accident_id' => $accident->id,
                        'file_name' => $file->getClientOriginalName()
                    ]);
                }
            }
        }

        Session::flash('success', 'Accident record added successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.accident-history', $validated['user_driver_detail_id']);
        }

        return redirect()->route('admin.accidents.index');
    }

    // Método para actualizar un accidente existente
    public function update(DriverAccident $accident, Request $request)
    {
        $validated = $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'accident_date' => 'required|date',
            'nature_of_accident' => 'required|string|max:255',
            'had_injuries' => 'boolean',
            'number_of_injuries' => 'nullable|integer|min:0',
            'had_fatalities' => 'boolean',
            'number_of_fatalities' => 'nullable|integer|min:0',
            'comments' => 'nullable|string',
            'documents.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        // Convertir checkboxes a valores booleanos
        $validated['had_injuries'] = isset($request->had_injuries);
        $validated['had_fatalities'] = isset($request->had_fatalities);

        // Solo incluir el número de lesiones/fatalidades si se marcó el checkbox
        if (!$validated['had_injuries']) {
            $validated['number_of_injuries'] = null;
        }
        if (!$validated['had_fatalities']) {
            $validated['number_of_fatalities'] = null;
        }

        $accident->update($validated);
        
        // Procesar los archivos subidos
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                try {
                    // Subir el archivo al accidente
                    $media = $accident->addMedia($file)
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('accident_documents');
                    
                    Log::info('Archivo de accidente actualizado correctamente', [
                        'accident_id' => $accident->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'collection' => 'accident_documents'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                        'accident_id' => $accident->id,
                        'file_name' => $file->getClientOriginalName()
                    ]);
                }
            }
        }

        Session::flash('success', 'Accident record updated successfully!');

        // Redirigir a la página apropiada
        if ($request->has('redirect_to_driver')) {
            return redirect()->route('admin.drivers.accident-history', $accident->user_driver_detail_id);
        }

        return redirect()->route('admin.accidents.index');
    }

    // Método para eliminar un accidente
    public function destroy(DriverAccident $accident)
    {
        $driverId = $accident->user_driver_detail_id;
        $accident->delete();

        Session::flash('success', 'Accident record deleted successfully!');

        // Determinar la ruta de retorno basado en la URL de referencia
        $referer = request()->headers->get('referer');
        if (strpos($referer, 'accident-history') !== false) {
            return redirect()->route('admin.drivers.accident-history', $driverId);
        }

        return redirect()->route('admin.accidents.index');
    }

    public function getDriversByCarrier(Carrier $carrier)
    {
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with(['user']) // Asegúrate de incluir la relación con el usuario
            ->get();

        // También puedes probar imprimiendo los datos para debug
        // dd($drivers);

        return response()->json($drivers);
    }
    
    /**
     * Mostrar los documentos de un accidente
     */
    public function showDocuments(DriverAccident $accident)
    {
        $documents = $accident->getMedia('accident_documents');
        
        return view('admin.drivers.accidents.documents', compact('accident', 'documents'));
    }
    
    /**
     * Eliminar un documento de un accidente
     */
    public function deleteDocument($documentId)
    {
        try {
            $media = Media::findOrFail($documentId);
            $accidentId = $media->model_id;
            
            // Verificar que el documento pertenece a un accidente
            if ($media->model_type !== DriverAccident::class) {
                return redirect()->back()->with('error', 'Invalid document type');
            }
            
            // Eliminar el documento
            $media->delete();
            
            return redirect()->back()->with('success', 'Document deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting document');
        }
    }
}
