<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use App\Models\Admin\Vehicle\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the maintenance records.
     */
    public function index()
    {
        // Obtener los próximos 5 mantenimientos ordenados por fecha de servicio
        $upcomingMaintenances = VehicleMaintenance::with('vehicle')
            ->where('status', 0) // Solo mantenimientos pendientes
            ->where('next_service_date', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('next_service_date', 'asc')
            ->take(5)
            ->get();
            
        // Contamos el total de mantenimientos programados para el mes actual
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        $totalScheduled = VehicleMaintenance::where('status', 0)
            ->whereYear('next_service_date', $currentYear)
            ->whereMonth('next_service_date', $currentMonth)
            ->count();
            
        return view('admin.vehicles.maintenance.index', compact('upcomingMaintenances', 'totalScheduled'));
    }

    /**
     * Show the form for creating a new maintenance record.
     */
    public function create()
    {
        // Obtener vehículos para el formulario
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Tipos de mantenimiento predefinidos
        $maintenanceTypes = [
            'Preventive',
            'Corrective',
            'Inspection',
            'Oil Change',
            'Tire Rotation',
            'Brake Service',
            'Engine Service',
            'Transmission Service',
            'Other'
        ];
        
        return view('admin.vehicles.maintenance.create', compact('vehicles', 'maintenanceTypes'));
    }

    /**
     * Store a newly created maintenance record in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        // Validar los datos del formulario
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:3|max:255',
            'service_tasks' => 'required|string|min:3|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Crear el registro de mantenimiento
            $maintenance = VehicleMaintenance::create([
                'vehicle_id' => $request->vehicle_id,
                'unit' => $request->unit,
                'service_tasks' => $request->service_tasks,
                'service_date' => $request->service_date,
                'next_service_date' => $request->next_service_date,
                'vendor_mechanic' => $request->vendor_mechanic,
                'cost' => $request->cost,
                'odometer' => $request->odometer,
                'description' => $request->description,
                'status' => $request->status ? 1 : 0,
                'created_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            // Procesar documentos subidos por Livewire (si hay)
            if ($request->filled('livewire_files')) {
                $this->processLivewireFiles($maintenance, json_decode($request->input('livewire_files'), true));
            }
            
            DB::commit();
            
            return redirect()->route('admin.maintenance.index')
                ->with('success', 'Registro de mantenimiento creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear registro de mantenimiento: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear el registro de mantenimiento. Por favor, inténtelo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified maintenance record.
     */
    public function edit($id)
    {
        // Verificar si el registro existe
        $maintenance = VehicleMaintenance::findOrFail($id);
        
        // Obtener vehículos para el formulario
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Tipos de mantenimiento predefinidos
        $maintenanceTypes = [
            'Preventive',
            'Corrective',
            'Inspection',
            'Oil Change',
            'Tire Rotation',
            'Brake Service',
            'Engine Service',
            'Transmission Service',
            'Other'
        ];
        
        return view('admin.vehicles.maintenance.edit', compact('maintenance', 'vehicles', 'maintenanceTypes'));
    }

    /**
     * Update the specified maintenance record in storage.
     */
    public function update(Request $request, $id)
    {       
         
        // Buscar el registro de mantenimiento
        $maintenance = VehicleMaintenance::findOrFail($id);
        
        // Validar los datos del formulario
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:3|max:255',
            'service_tasks' => 'required|string|min:3|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'vendor_mechanic' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Actualizar el registro de mantenimiento
            $maintenance->update([
                'vehicle_id' => $request->vehicle_id,
                'unit' => $request->unit,
                'service_tasks' => $request->service_tasks,
                'service_date' => $request->service_date,
                'next_service_date' => $request->next_service_date,
                'vendor_mechanic' => $request->vendor_mechanic,
                'cost' => $request->cost,
                'odometer' => $request->odometer,
                'description' => $request->description,
                'status' => $request->status ? true : false,
                'updated_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            // Procesar documentos subidos por Livewire (si hay)
            if ($request->filled('livewire_files')) {
                $this->processLivewireFiles($maintenance, json_decode($request->input('livewire_files'), true));
            }
            
            DB::commit();
            
            return redirect()->route('admin.maintenance.index')
                ->with('success', 'Registro de mantenimiento actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar registro de mantenimiento: ' . $e->getMessage(), [
                'exception' => $e,
                'maintenance_id' => $id,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar el registro de mantenimiento. Por favor, inténtelo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Display the specified maintenance record.
     */
    public function show($id)
    {
        // Buscar el mantenimiento con su relación de vehículo
        $maintenance = VehicleMaintenance::with(['vehicle', 'vehicle.carrier', 'media'])->findOrFail($id);
        $vehicle = $maintenance->vehicle;
        
        return view('admin.vehicles.maintenance.show', compact('maintenance', 'vehicle'));
    }
    
    /**
     * Reprogramar un mantenimiento existente
     */
    public function reschedule(Request $request, $id)
    {
        try {
            $request->validate([
                'next_service_date' => 'required|date|after:today',
                'reschedule_reason' => 'required|string|min:3|max:500',
            ]);
            
            $maintenance = VehicleMaintenance::findOrFail($id);
            
            // Guardar la fecha anterior para el registro
            $previousDate = $maintenance->next_service_date;
            
            // Actualizar la fecha
            $maintenance->next_service_date = $request->next_service_date;
            
            // Agregar nota sobre reprogramación
            $noteText = "[" . now()->format('Y-m-d H:i:s') . "] Reprogramado del " . 
                Carbon::parse($previousDate)->format('d/m/Y') . " al " . 
                Carbon::parse($request->next_service_date)->format('d/m/Y') . ". \nMotivo: " . 
                $request->reschedule_reason;
            
            // Manejar el caso cuando notes es null (para registros antiguos)
            if (empty($maintenance->notes)) {
                $maintenance->notes = $noteText;
            } else {
                $maintenance->notes = $maintenance->notes . "\n\n" . $noteText;
            }
            
            $maintenance->save();
            
            return redirect()->route('admin.maintenance.show', $id)
                ->with('success', 'Mantenimiento reprogramado correctamente para el ' . 
                    Carbon::parse($request->next_service_date)->format('d/m/Y'));
        } catch (\Exception $e) {
            Log::error('Error al reprogramar mantenimiento: ' . $e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
                'exception' => $e
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al reprogramar el mantenimiento: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Toggle maintenance status (completed/pending)
     */
    public function toggleStatus($id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);
        $maintenance->status = !$maintenance->status;
        $maintenance->save();
        
        return back()->with('success', 'Estado del mantenimiento actualizado.');
    }
    
    /**
     * Delete a maintenance record
     */
    public function destroy($id)
    {
        $maintenance = VehicleMaintenance::findOrFail($id);
        $maintenance->delete();
        
        return redirect()->route('admin.maintenance.index')
                ->with('success', 'Registro de mantenimiento eliminado correctamente');
    }
    
    /**
     * Export maintenance records to Excel
     */
    public function export()
    {
        // Para futura implementación de exportación
        // return (new VehicleMaintenanceExport)->download('vehicle-maintenance.xlsx');
        
        return redirect()->route('admin.maintenance.index')
            ->with('info', 'La funcionalidad de exportación estará disponible próximamente');
    }
    
    /**
     * Show maintenance reports.
     */
    public function reports()
    {
        // Para futura implementación de reportes
        return view('admin.vehicles.maintenance.reports');
    }
    
    /**
     * Show maintenance calendar.
     */
    public function calendar(Request $request)
    {
        // Aplicar filtros si están presentes
        $query = VehicleMaintenance::with('vehicle');
        
        // Filtrar por vehículo si se especificó
        if ($request->has('vehicle_id') && $request->vehicle_id) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        // Filtrar por estado si se especificó
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Obtener todos los mantenimientos con filtros aplicados
        $maintenances = $query->get();
            
        // Convertir mantenimientos al formato de eventos para el calendario
        $events = [];
        // Para almacenar los próximos mantenimientos
        $upcomingMaintenances = [];
        $today = Carbon::today();
        
        foreach ($maintenances as $maintenance) {
            // Definir la clase CSS según el estado (completado o pendiente)
            $className = $maintenance->status ? 'maintenance-completed' : 'maintenance-pending';
            
            // Obtener información del vehículo si está disponible
            $vehicleInfo = $maintenance->vehicle ? 
                $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model . 
                ' (' . $maintenance->vehicle->year . ') - ' . $maintenance->vehicle->plate_number : 
                'Vehículo no especificado';
                
            // Formatear fechas para mostrar
            $serviceDateFormatted = Carbon::parse($maintenance->service_date)->format('d/m/Y');
            
            // Crear evento para la fecha de servicio
            $events[] = [
                'id' => 'service-' . $maintenance->id,
                'title' => $maintenance->service_tasks . ' - ' . $vehicleInfo,
                'start' => $maintenance->service_date, // Formato YYYY-MM-DD
                'className' => $className,
                'extendedProps' => [
                    'vehicle' => $vehicleInfo,
                    'serviceType' => $maintenance->service_tasks,
                    'serviceDate' => $serviceDateFormatted,
                    'status' => $maintenance->status,
                    'cost' => '$' . number_format($maintenance->cost, 2),
                    'description' => $maintenance->description ?? 'Sin descripción'
                ]
            ];
            
            // Crear evento para la próxima fecha de servicio si existe
            if ($maintenance->next_service_date) {
                $nextServiceDate = Carbon::parse($maintenance->next_service_date);
                $nextServiceDateFormatted = $nextServiceDate->format('d/m/Y');
                
                // Si la próxima fecha de servicio es en el futuro, añadirla a los próximos mantenimientos
                if ($nextServiceDate->gt($today)) {
                    // Agregar este mantenimiento a la lista de próximos
                    $maintenance->next_service_formatted = $nextServiceDateFormatted;
                    $upcomingMaintenances[] = $maintenance;
                }
                
                $events[] = [
                    'id' => 'next-service-' . $maintenance->id,
                    'title' => 'Próximo: ' . $maintenance->service_tasks . ' - ' . $vehicleInfo,
                    'start' => $maintenance->next_service_date, // Formato YYYY-MM-DD
                    'className' => 'maintenance-upcoming',
                    'extendedProps' => [
                        'vehicle' => $vehicleInfo,
                        'serviceType' => $maintenance->service_tasks,
                        'serviceDate' => $nextServiceDateFormatted,
                        'status' => 2, // 2 para próximos mantenimientos
                        'cost' => 'Por definir',
                        'description' => 'Próximo mantenimiento programado'
                    ]
                ];
            }
        }
        
        // Ordenar los próximos mantenimientos por fecha
        $upcomingMaintenances = collect($upcomingMaintenances)
            ->sortBy(function ($maintenance) {
                return Carbon::parse($maintenance->next_service_date)->timestamp;
            })
            ->take(5); // Mostrar solo los próximos 5 mantenimientos
        
        // Obtener todos los vehículos para el filtro
        $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
        
        // Pasar el estado seleccionado de vuelta a la vista
        $status = $request->status;
        $vehicleId = $request->vehicle_id;
            
        return view('admin.vehicles.maintenance.calendar', compact(
            'events', 
            'upcomingMaintenances', 
            'vehicles', 
            'status', 
            'vehicleId'
        ));
    }
    
    /**
     * Subir documentos para un mantenimiento específico usando Spatie Media Library
     * 
     * @param VehicleMaintenance $maintenance El mantenimiento al que se subirán los documentos
     * @param Request $request Solicitud con los documentos a subir o JSON de archivos temporales de Livewire
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function storeDocuments(VehicleMaintenance $maintenance, Request $request)
    {
        try {
            DB::beginTransaction();
            
            $uploadedCount = 0;
            $errors = [];
            
            if ($request->hasFile('documents')) {
                // Método tradicional con archivos subidos directamente
                foreach ($request->file('documents') as $document) {
                    try {
                        // Subir archivo usando Media Library
                        $media = $maintenance->addMedia($document->getPathname())
                            ->usingName($document->getClientOriginalName())
                            ->withCustomProperties([
                                'maintenance_id' => $maintenance->id,
                                'vehicle_id' => $maintenance->vehicle_id,
                                'uploaded_at' => now()->format('Y-m-d H:i:s'),
                                'original_name' => $document->getClientOriginalName()
                            ])
                            ->toMediaCollection('maintenance_files');
                        
                        $uploadedCount++;
                        
                        Log::info('Documento de mantenimiento subido correctamente', [
                            'maintenance_id' => $maintenance->id,
                            'media_id' => $media->id,
                            'file_name' => $media->file_name
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Error al subir {$document->getClientOriginalName()}: {$e->getMessage()}";
                    }
                }
            } elseif ($request->filled('livewire_files')) {
                // Método Livewire con archivos temporales
                $livewireFiles = json_decode($request->input('livewire_files'), true);
                
                if (!is_array($livewireFiles) || empty($livewireFiles)) {
                    return redirect()->back()->with('error', 'No se recibieron archivos válidos');
                }
                
                // Procesar los archivos temporales de Livewire
                foreach ($livewireFiles as $fileData) {
                    // Verificar que tenemos la información necesaria
                    if (!isset($fileData['path']) || !isset($fileData['name'])) {
                        $errors[] = 'Datos de archivo incompletos';
                        continue;
                    }
                    
                    $tempPath = storage_path('app/' . $fileData['path']);
                    
                    // Verificar que el archivo temporal existe
                    if (!file_exists($tempPath)) {
                        $errors[] = "Archivo temporal no encontrado: {$fileData['name']}";
                        continue;
                    }
                    
                    try {
                        // Subir desde el archivo temporal a Media Library
                        $media = $maintenance->addMedia($tempPath)
                            ->usingName($fileData['name'])
                            ->withCustomProperties([
                                'maintenance_id' => $maintenance->id,
                                'vehicle_id' => $maintenance->vehicle_id,
                                'uploaded_at' => now()->format('Y-m-d H:i:s'),
                                'original_name' => $fileData['name']
                            ])
                            ->toMediaCollection('maintenance_files');
                        
                        $uploadedCount++;
                        
                        Log::info('Documento de mantenimiento subido desde Livewire', [
                            'maintenance_id' => $maintenance->id,
                            'media_id' => $media->id,
                            'file_name' => $media->file_name,
                            'original_name' => $fileData['name']
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Error al procesar {$fileData['name']}: {$e->getMessage()}";
                        Log::error('Error al procesar archivo temporal', [
                            'file' => $fileData,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'No se recibieron archivos para subir');
            }
            
            DB::commit();
            
            $message = "$uploadedCount documentos subidos correctamente";
            if (!empty($errors)) {
                $message .= ", pero hubo errores con algunos archivos: " . implode(", ", $errors);
                return redirect()->back()->with('warning', $message);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al subir documentos de mantenimiento', [
                'maintenance_id' => $maintenance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

        }
    }
    
    /**
     * Procesar los archivos subidos por Livewire
     */
    private function processLivewireFiles($maintenance, $filesData)
    {
        if (!$filesData) {
            return;
        }

        // Loguear para depuración
        Log::info('Datos originales recibidos en processLivewireFiles', [
            'raw_data' => $filesData,
            'type' => gettype($filesData)
        ]);

        // Si los datos llegan como un string JSON
        if (is_string($filesData)) {
            try {
                $filesData = json_decode($filesData, true);
                Log::info('Datos después de decodificar JSON', [
                    'filesData' => $filesData
                ]);
            } catch (\Exception $e) {
                Log::error('Error decodificando JSON de archivos', ['error' => $e->getMessage()]);
                return;
            }
        }
        
        if (!is_array($filesData)) {
            Log::error('Formato de datos de archivos inválido', ['filesData' => $filesData]);
            return;
        }

        // Extraer todos los archivos, independientemente del formato
        $extractedFiles = [];
        
        // Procesar formato [[$file1], [$file2], ...]
        foreach ($filesData as $item) {
            if (is_array($item) && count($item) === 1 && isset($item[0])) {
                // Formato [$file] - extraer el elemento
                $extractedFiles[] = $item[0];
            } elseif (is_array($item)) {
                // Añadir directamente
                $extractedFiles[] = $item;
            }
        }
        
        // Si no extrajimos nada, intentar con el formato original
        if (empty($extractedFiles)) {
            $extractedFiles = $filesData;
        }
        
        Log::info('Archivos extraídos para procesar', [
            'count' => count($extractedFiles),
            'extractedFiles' => $extractedFiles
        ]);
        
        // Procesar cada archivo extraído
        foreach ($extractedFiles as $fileData) {
            $tempPath = null;
            $fileName = null;
            $mimeType = null;
            $size = null;
            $originalName = null;
            
            // Detectar formato del archivo
            if (isset($fileData['tempPath'])) {
                $tempPath = storage_path('app/' . $fileData['tempPath']);
                $fileName = $fileData['originalName'] ?? null;
                $mimeType = $fileData['mimeType'] ?? null;
                $size = $fileData['size'] ?? null;
                $originalName = $fileData['originalName'] ?? null;
            } elseif (isset($fileData['path'])) {
                $tempPath = storage_path('app/' . $fileData['path']);
                $fileName = $fileData['name'] ?? null;
                $mimeType = $fileData['mime_type'] ?? null;
                $size = $fileData['size'] ?? null;
                $originalName = $fileData['name'] ?? null;
            }
            
            // Si no tenemos nombre pero hay datos de preview, usarlos
            if (isset($fileData['previewData'])) {
                $fileName = $fileName ?? $fileData['previewData']['name'] ?? null;
                $mimeType = $mimeType ?? $fileData['previewData']['mime_type'] ?? null;
                $size = $size ?? $fileData['previewData']['size'] ?? null;
                $originalName = $originalName ?? $fileData['previewData']['name'] ?? null;
            }
            
            // Verificar si tenemos los datos mínimos necesarios
            if ($tempPath && $fileName && file_exists($tempPath)) {
                try {
                    // Añadir el archivo a la colección de medios
                    $maintenance->addMedia($tempPath)
                        ->usingName($fileName)
                        ->usingFileName($fileName)
                        ->withCustomProperties([
                            'maintenance_id' => $maintenance->id,
                            'vehicle_id' => $maintenance->vehicle_id,
                            'uploaded_at' => now()->format('Y-m-d H:i:s'),
                            'original_name' => $originalName,
                            'mime_type' => $mimeType,
                            'size' => $size
                        ])
                        ->toMediaCollection('maintenance_files');
                    
                    Log::info('Archivo procesado correctamente', [
                        'maintenance_id' => $maintenance->id,
                        'file_name' => $fileName,
                        'temp_path' => $tempPath
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error procesando archivo', [
                        'maintenance_id' => $maintenance->id,
                        'file_name' => $fileName,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('Archivo temporal no encontrado o datos inválidos', [
                    'tempPath' => $tempPath ?? 'No proporcionado',
                    'fileName' => $fileName ?? 'No proporcionado',
                    'exists' => $tempPath ? file_exists($tempPath) : false,
                    'fileData' => $fileData
                ]);
            }
        }
    }
    
    /**
     * Eliminar un documento de mantenimiento vía AJAX
     * 
     * @param int $document ID del media a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDeleteDocument($document)
    {
        try {
            // Buscar el archivo por ID
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($document);
            
            if (!$media) {
                return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
            }
            
            // Verificar que el archivo pertenece a una colección de mantenimiento
            if ($media->collection_name !== 'maintenance_files') {
                return response()->json(['success' => false, 'message' => 'Archivo no pertenece a mantenimiento'], 400);
            }
            
            // Guardar información antes de eliminar para logging
            $mediaInfo = [
                'id' => $media->id,
                'file_name' => $media->file_name,
                'collection' => $media->collection_name,
                'custom_properties' => $media->custom_properties
            ];
            
            // Eliminar el archivo
            $media->delete();
            
            Log::info('Archivo de mantenimiento eliminado correctamente vía AJAX', $mediaInfo);
            
            return response()->json([
                'success' => true, 
                'message' => 'Archivo eliminado correctamente',
                'deleted_file' => $mediaInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo de mantenimiento', [
                'document_id' => $document,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}