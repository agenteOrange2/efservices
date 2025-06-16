<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\Training;
use App\Models\Admin\Driver\DriverTraining;
use App\Models\UserDriverDetail;

class TrainingAssignmentsController extends Controller
{
    /**
     * Display a listing of training assignments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = DriverTraining::with(['driver', 'training', 'driver.carrier']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('driver', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhereHas('training', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->whereHas('driver', function ($q) use ($request) {
                $q->where('carrier_id', $request->input('carrier_id'));
            });
        }

        if ($request->filled('training_id')) {
            $query->where('training_id', $request->input('training_id'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Sort
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Handle special sorting cases
        if ($sortField === 'driver_name') {
            $query->join('drivers', 'driver_trainings.driver_id', '=', 'drivers.id')
                ->orderBy('drivers.first_name', $sortDirection)
                ->orderBy('drivers.last_name', $sortDirection);
        } elseif ($sortField === 'training_title') {
            $query->join('trainings', 'driver_trainings.training_id', '=', 'trainings.id')
                ->orderBy('trainings.title', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $assignments = $query->paginate(15);
        $carriers = Carrier::where('status', 'active')->get();
        $trainings = Training::where('status', 'active')->get();

        return view('admin.drivers.trainings.assignments.index', compact('assignments', 'carriers', 'trainings'));
    }

    /**
     * Display the specified assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            Log::info('Intentando obtener detalles de asignación con ID: ' . $id);

            // Intentar primero con el modelo Eloquent
            $assignment = DriverTraining::with(['driver.user', 'driver.carrier', 'training', 'training.media'])
                ->where('id', $id)
                ->first();

            if (!$assignment) {
                Log::warning('No se encontró la asignación con ID: ' . $id);

                // Intentar con DB::table como respaldo
                $rawAssignment = DB::table('driver_trainings')
                    ->where('id', $id)
                    ->first();

                if (!$rawAssignment) {
                    return response()->json(['error' => 'No se encontró la asignación especificada.'], 404);
                }

                // Construir una respuesta mínima con los datos disponibles
                Log::info('Asignación encontrada solo en DB: ' . $rawAssignment->id);

                return response()->json([
                    'id' => $rawAssignment->id,
                    'driver_id' => $rawAssignment->driver_id,
                    'training_id' => $rawAssignment->training_id,
                    'status' => $rawAssignment->status,
                    'status_label' => ucfirst($rawAssignment->status),
                    'created_at_formatted' => $rawAssignment->created_at ? date('d/m/Y H:i', strtotime($rawAssignment->created_at)) : 'N/A',
                    'due_date_formatted' => $rawAssignment->due_date ? date('d/m/Y', strtotime($rawAssignment->due_date)) : null,
                    'completed_at_formatted' => $rawAssignment->completed_at ? date('d/m/Y H:i', strtotime($rawAssignment->completed_at)) : null,
                    'notes' => $rawAssignment->notes,
                    '_raw' => true,
                    '_message' => 'Datos básicos recuperados de DB::table'
                ]);
            }

            Log::info('Asignación Eloquent encontrada: ' . $assignment->id);

            // Registrar datos completos para debug
            Log::info('Datos completos de la asignación: ' . json_encode([
                'asignacion_id' => $assignment->id,
                'driver' => $assignment->driver ? json_encode([
                    'id' => $assignment->driver->id,
                    'user' => $assignment->driver->user ? $assignment->driver->user->name : 'Usuario no disponible',
                    'carrier' => $assignment->driver->carrier ? $assignment->driver->carrier->name : 'Carrier no disponible'
                ]) : 'Driver no disponible',
                'training' => $assignment->training ? $assignment->training->title : 'Training no disponible'
            ]));

            // Formatear fechas para mostrar
            $created_at_formatted = $assignment->created_at ? $assignment->created_at->format('d/m/Y H:i') : 'N/A';
            $due_date_formatted = $assignment->due_date ? date('d/m/Y', strtotime($assignment->due_date)) : null;
            $completed_at_formatted = $assignment->completed_at ? date('d/m/Y H:i', strtotime($assignment->completed_at)) : null;

            // Obtener etiqueta de estado
            $status_labels = [
                'assigned' => 'Assigned',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'overdue' => 'Overdue'
            ];

            $status_label = $status_labels[$assignment->status] ?? 'Unknown';

            return response()->json([
                'id' => $assignment->id,
                'driver' => $assignment->driver,
                'training' => $assignment->training,
                'status' => $assignment->status,
                'status_label' => $status_label,
                'created_at_formatted' => $created_at_formatted,
                'due_date_formatted' => $due_date_formatted,
                'completed_at_formatted' => $completed_at_formatted,
                'notes' => $assignment->notes,
                'completed_by' => null,
                'media' => $assignment->training ? $assignment->training->media : [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de la asignación: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error al cargar los detalles: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark an assignment as complete or revert its status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DriverTraining  $assignment
     * @return \Illuminate\Http\Response
     */
    public function markComplete(Request $request, DriverTraining $assignment)
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'revert' => 'nullable|boolean',
        ]);

        try {
            // Si se solicita revertir el estado
            if ($request->has('revert') && $request->input('revert')) {
                $assignment->update([
                    'status' => 'assigned',
                    'completed_date' => null,
                    'completion_notes' => null,
                ]);

                return redirect()->route('admin.training-assignments.index')
                    ->with('success', 'Training status reverted successfully.');
            } else {
                // Marcar como completado
                $assignment->update([
                    'status' => 'completed',
                    'completed_date' => now(),
                    'completion_notes' => $validated['completion_notes'] ?? null,
                ]);

                return redirect()->route('admin.training-assignments.index')
                    ->with('success', 'Training marked as completed successfully.');
            }
        } catch (\Exception $e) {
            Log::error('Error updating training status: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Error updating training status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified assignment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Registrar información detallada antes de eliminar            

            // Buscar la asignación en la base de datos manualmente
            $assignment = DB::table('driver_trainings')->where('id', $id)->first();

            if (!$assignment) {
                return redirect()->route('admin.training-assignments.index')
                    ->with('error', 'No se encontró la asignación especificada.');
            }

            // Usar DB::table directamente para la eliminación
            $result = DB::table('driver_trainings')->where('id', $id)->delete();

            // Verificar si se eliminó
            $exists = DB::table('driver_trainings')->where('id', $id)->exists();

            if ($result) {
                return redirect()->route('admin.training-assignments.index')
                    ->with('success', 'La asignación de entrenamiento se eliminó correctamente.');
            } else {
                return redirect()->route('admin.training-assignments.index')
                    ->with('error', 'No se pudo eliminar la asignación. Por favor, inténtelo de nuevo.');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.training-assignments.index')
                ->with('error', 'Error al eliminar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for assigning training to drivers.
     *
     * @param  \App\Models\Admin\Driver\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function showAssignForm(Training $training)
    {
        // Usar la constante STATUS_ACTIVE del modelo Carrier
        $carriers = Carrier::where('status', Carrier::STATUS_ACTIVE)->get();
        $selectedTraining = $training;
        $trainings = Training::where('status', 'active')->get();
        return view('admin.drivers.trainings.assign', compact('selectedTraining', 'carriers', 'trainings'));
    }

    /**
     * Assign training to drivers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Driver\Training  $training
     * @return \Illuminate\Http\Response
     */
    public function assign(Request $request, Training $training)
    {
        $validated = $request->validate([
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:user_driver_details,id',
            'due_date' => 'nullable|date',
            'status' => 'required|in:assigned,in_progress,completed',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $assignedCount = 0;
            $alreadyAssignedCount = 0;

            foreach ($validated['driver_ids'] as $driverId) {
                // Check if already assigned
                $exists = DriverTraining::where('user_driver_detail_id', $driverId)
                    ->where('training_id', $training->id)
                    ->exists();

                if (!$exists) {
                    DriverTraining::create([
                        'user_driver_detail_id' => $driverId,
                        'training_id' => $training->id,
                        'assigned_date' => now(),
                        'due_date' => $validated['due_date'],
                        'status' => $validated['status'],
                        'completion_notes' => $validated['notes'],
                        'assigned_by' => Auth::id(),
                    ]);
                    $assignedCount++;
                } else {
                    $alreadyAssignedCount++;
                }
            }

            DB::commit();

            $message = "{$assignedCount} drivers assigned successfully.";
            if ($alreadyAssignedCount > 0) {
                $message .= " {$alreadyAssignedCount} drivers were already assigned.";
            }

            return redirect()->route('admin.training-assignments.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning training: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Error assigning training: ' . $e->getMessage());
        }
    }

    /**
     * Get drivers filtered by carrier ID.
     * If carrier ID is 0, returns all active drivers.
     *
     * @param  int  $carrier
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrivers($carrier)
    {
        $query = UserDriverDetail::query()
            ->whereHas('user', function ($query) {
                $query->where('status', 1); // Only active users
            })
            ->where('status', UserDriverDetail::STATUS_ACTIVE); // Usar la constante para conductores activos

        // Si carrier_id no es 0, filtra por la transportista específica
        if ($carrier != 0) {
            $query->where('carrier_id', $carrier);
        }

        // Incluir información del carrier para mostrar en el selector
        $drivers = $query->with(['user', 'carrier'])
            ->get();

        // Devolver directamente los conductores como array JSON
        return response()->json($drivers);
    }
}
