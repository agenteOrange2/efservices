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
        // Validación condicional basada en si es un servicio histórico
        $isHistorical = $request->boolean('is_historical');
        
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:1|max:255',
            'service_tasks' => 'required|string|min:1|max:255',
            'service_date' => 'required|date',
            'vendor_mechanic' => 'required|string|min:1|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'is_historical' => 'nullable|boolean'
        ];
        
        // Ajustar validación de fechas según si es histórico o no
        if ($isHistorical) {
            // Para servicios históricos, permitir fechas pasadas y next_service_date puede ser anterior a service_date
            $validationRules['next_service_date'] = 'required|date';
        } else {
            // Para servicios normales, mantener validación original
            $validationRules['service_date'] .= '|before_or_equal:today';
            $validationRules['next_service_date'] = 'required|date|after:service_date';
        }
        
        // Validar los datos del formulario
        $validated = $request->validate($validationRules);
        
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
                'is_historical' => $request->boolean('is_historical'),
                'created_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            // Procesar documentos subidos por Livewire (si hay)
            if ($request->filled('livewire_files')) {
                $this->processLivewireFiles($maintenance, json_decode($request->input('livewire_files'), true));
            }
            
            DB::commit();
            
            return redirect()->route('admin.maintenance.index')
                ->with('maintenance_success', 'Registro de mantenimiento creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear registro de mantenimiento: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('maintenance_error', 'Error al crear el registro de mantenimiento. Por favor, inténtelo de nuevo.')
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
        
        // Validación condicional basada en si es un servicio histórico
        $isHistorical = $request->boolean('is_historical');
        
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'unit' => 'required|string|min:1|max:255',
            'service_tasks' => 'required|string|min:1|max:255',
            'service_date' => 'required|date',
            'vendor_mechanic' => 'required|string|min:1|max:255',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|boolean',
            'is_historical' => 'nullable|boolean'
        ];
        
        // Ajustar validación de fechas según si es histórico o no
        if ($isHistorical) {
            // Para servicios históricos, permitir fechas pasadas y next_service_date puede ser anterior a service_date
            $validationRules['next_service_date'] = 'required|date';
        } else {
            // Para servicios normales, mantener validación original
            $validationRules['service_date'] .= '|before_or_equal:today';
            $validationRules['next_service_date'] = 'required|date|after:service_date';
        }
        
        // Validar los datos del formulario
        $validated = $request->validate($validationRules);
        
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
                'is_historical' => $request->boolean('is_historical'),
                'updated_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            // Procesar documentos subidos por Livewire (si hay)
            if ($request->filled('livewire_files')) {
                $this->processLivewireFiles($maintenance, json_decode($request->input('livewire_files'), true));
            }
            
            DB::commit();
            
            return redirect()->route('admin.maintenance.index')
                ->with('maintenance_success', 'Registro de mantenimiento actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar registro de mantenimiento: ' . $e->getMessage(), [
                'exception' => $e,
                'maintenance_id' => $id,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('maintenance_error', 'Error al actualizar el registro de mantenimiento. Por favor, inténtelo de nuevo.')
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
                ->with('maintenance_success', 'Mantenimiento reprogramado correctamente para el ' . 
                    Carbon::parse($request->next_service_date)->format('d/m/Y'));
        } catch (\Exception $e) {
            Log::error('Error al reprogramar mantenimiento: ' . $e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
                'exception' => $e
            ]);
            
            return redirect()->back()
                ->with('maintenance_error', 'Error al reprogramar el mantenimiento: ' . $e->getMessage())
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
        
        return back()->with('maintenance_success', 'Estado del mantenimiento actualizado.');
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
                return redirect()->back()->with('maintenance_error', 'No se recibieron archivos para subir');
            }
            
            DB::commit();
            
            $message = "$uploadedCount documentos subidos correctamente";
            if (!empty($errors)) {
                $message .= ", pero hubo errores con algunos archivos: " . implode(", ", $errors);
                return redirect()->back()->with('maintenance_message', $message);
            }
            
            return redirect()->back()->with('maintenance_success', $message);
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

    /**
     * Display the maintenance calendar view
     *
     * @return \Illuminate\View\View
     */
    public function calendar()
    {
        try {
            // Get all maintenance records with their vehicles for the calendar
            $maintenances = VehicleMaintenance::with('vehicle')
                ->orderBy('next_service_date', 'asc')
                ->get();

            // Format data for calendar events
            $events = $maintenances->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'title' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model . ' - ' . $maintenance->service_tasks,
                    'start' => $maintenance->next_service_date->format('Y-m-d'),
                    'end' => $maintenance->next_service_date->format('Y-m-d'),
                    'backgroundColor' => $this->getStatusColor($maintenance->status),
                    'borderColor' => $this->getStatusColor($maintenance->status),
                    'url' => route('admin.maintenance.edit', $maintenance->id),
                    'extendedProps' => [
                        'status' => $maintenance->status ? 'completed' : 'pending',
                        'vehicle' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                        'type' => $maintenance->service_tasks,
                        'cost' => $maintenance->cost
                    ]
                ];
            });

            return view('admin.vehicles.maintenance.calendar', compact('events'));
        } catch (\Exception $e) {
            Log::error('Error loading maintenance calendar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('maintenance_error', 'Error al cargar el calendario de mantenimiento');
        }
    }

    /**
     * Export maintenance reports to PDF.
     */
    public function exportPdf(Request $request)
    {
        $period = $request->input('period', 'all');
        $vehicleId = $request->input('vehicle_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        // Build the query - same logic as reports method
        $query = VehicleMaintenance::with(['vehicle'])
            ->orderBy('service_date', 'desc');

        // Apply vehicle filter
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        // Apply status filter - fix the status comparison
        if ($status !== null && $status !== '') {
            $query->where('status', $status == '1' ? true : false);
        }

        // Apply date filters based on period - fix the date filtering logic
        switch ($period) {
            case 'daily':
                $query->whereDate('service_date', now()->format('Y-m-d'));
                break;
            case 'weekly':
                $query->whereBetween('service_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereBetween('service_date', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'yearly':
                $query->whereBetween('service_date', [now()->startOfYear(), now()->endOfYear()]);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    $query->whereBetween('service_date', [$startDate, $endDate]);
                }
                break;
            case 'all':
            default:
                // No date filter - show all records
                break;
        }

        $maintenances = $query->get();
        
        // Debug: Log the query and results
        \Log::info('PDF Export Debug', [
            'period' => $period,
            'vehicleId' => $vehicleId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'query_count' => $maintenances->count(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
        
        // Calculate statistics
        $totalMaintenances = $maintenances->count();
        $vehiclesServiced = $maintenances->pluck('vehicle_id')->unique()->count();
        $totalCost = $maintenances->sum('cost');
        $avgCostPerVehicle = $vehiclesServiced > 0 ? $totalCost / $vehiclesServiced : 0;

        // Generate PDF using DomPDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.vehicles.maintenance.reports-pdf', compact(
            'maintenances',
            'totalMaintenances',
            'vehiclesServiced', 
            'totalCost',
            'avgCostPerVehicle',
            'period',
            'startDate',
            'endDate'
        ));

        $filename = 'maintenance-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get calendar events as JSON for AJAX requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents()
    {
        try {
            // Get all maintenance records with their vehicles for the calendar
            $maintenances = VehicleMaintenance::with('vehicle')
                ->orderBy('next_service_date', 'asc')
                ->get();

            // Format data for calendar events
            $events = $maintenances->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'title' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model . ' - ' . $maintenance->service_tasks,
                    'start' => $maintenance->next_service_date->format('Y-m-d'),
                    'end' => $maintenance->next_service_date->format('Y-m-d'),
                    'backgroundColor' => $this->getStatusColor($maintenance->status),
                    'borderColor' => $this->getStatusColor($maintenance->status),
                    'url' => route('admin.maintenance.edit', $maintenance->id),
                    'extendedProps' => [
                        'status' => $maintenance->status ? 'completed' : 'pending',
                        'vehicle' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                        'type' => $maintenance->service_tasks,
                        'cost' => $maintenance->cost
                    ]
                ];
            });

            return response()->json($events);
        } catch (\Exception $e) {
            Log::error('Error loading maintenance calendar events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([], 500);
        }
    }

    /**
     * Display the maintenance reports view
     *
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        try {
            // Get filter parameters
            $period = $request->get('period', 'all');
            $vehicleId = $request->get('vehicle_id');
            $status = $request->get('status');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Build base query for filtered data
            $filteredQuery = VehicleMaintenance::with('vehicle');
            
            if ($vehicleId) {
                $filteredQuery->where('vehicle_id', $vehicleId);
            }
            
            if ($status !== null && $status !== '') {
                $filteredQuery->where('status', $status == '1' ? true : false);
            }
            
            // Apply date filters based on period
            switch ($period) {
                case 'daily':
                    $filteredQuery->whereDate('service_date', now()->format('Y-m-d'));
                    break;
                case 'weekly':
                    $filteredQuery->whereBetween('service_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'monthly':
                    $filteredQuery->whereBetween('service_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'yearly':
                    $filteredQuery->whereBetween('service_date', [now()->startOfYear(), now()->endOfYear()]);
                    break;
                case 'custom':
                    if ($startDate && $endDate) {
                        $filteredQuery->whereBetween('service_date', [$startDate, $endDate]);
                    }
                    break;
                case 'all':
                default:
                    // No date filter - show all records
                    break;
            }
            
            // Get paginated results for the table
            $maintenances = $filteredQuery->paginate(15);
            
            // Calculate statistics from ALL filtered records (not just paginated)
            $allFilteredMaintenances = $filteredQuery->get();
            $totalMaintenances = $allFilteredMaintenances->count();
            $totalVehiclesServiced = $allFilteredMaintenances->pluck('vehicle_id')->unique()->count();
            $totalCost = $allFilteredMaintenances->sum('cost');
            $avgCostPerVehicle = $totalVehiclesServiced > 0 ? $totalCost / $totalVehiclesServiced : 0;
            
            // Get service type distribution from all filtered records
            $serviceTypeDistribution = [];
            $serviceTypes = $allFilteredMaintenances->pluck('service_tasks')->filter()->countBy();
            $totalServices = $serviceTypes->sum();
            
            foreach ($serviceTypes as $type => $count) {
                $serviceTypeDistribution[$type] = [
                    'count' => $count,
                    'percentage' => $totalServices > 0 ? ($count / $totalServices) * 100 : 0
                ];
            }
            
            // Get upcoming maintenances (always from all records, not filtered)
            $upcomingMaintenances = VehicleMaintenance::with('vehicle')
                ->where('status', false)
                ->where('next_service_date', '>=', now())
                ->orderBy('next_service_date', 'asc')
                ->limit(10)
                ->get();
            
            // Get all vehicles for filter dropdown
            $vehicles = Vehicle::orderBy('make')->orderBy('model')->get();
            
            return view('admin.vehicles.maintenance.reports', compact(
                'maintenances',
                'totalMaintenances',
                'totalCost',
                'vehicles',
                'period',
                'vehicleId',
                'status',
                'startDate',
                'endDate',
                'serviceTypeDistribution',
                'upcomingMaintenances',
                'avgCostPerVehicle'
            ), [
                'vehiclesServiced' => $totalVehiclesServiced
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading maintenance reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al cargar los reportes de mantenimiento');
        }
    }

    /**
     * Get color based on maintenance status
     *
     * @param bool $status
     * @return string
     */
    private function getStatusColor($status)
    {
        if ($status) {
            return '#28a745'; // Green - Completed
        } else {
            return '#dc3545'; // Red - Pending
        }
    }
}