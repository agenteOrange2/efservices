<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Driver\DriverTraining;
use App\Models\Admin\Driver\Training;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * @param  \App\Models\DriverTraining  $assignment
     * @return \Illuminate\Http\Response
     */
    public function show(DriverTraining $assignment)
    {   
        $assignment->load(['driver', 'training', 'training.media']);
        return response()->json([
            'assignment' => $assignment,
            'driver' => $assignment->driver,
            'training' => $assignment->training,
            'media' => $assignment->training->media,
        ]);
    }
    
    /**
     * Mark an assignment as complete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DriverTraining  $assignment
     * @return \Illuminate\Http\Response
     */
    public function markComplete(Request $request, DriverTraining $assignment)
    {   
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);
        
        try {
            $assignment->update([
                'status' => 'completed',
                'completion_date' => now(),
                'completion_notes' => $validated['completion_notes'] ?? null,
                'completed_by' => Auth::id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Training marked as completed successfully.',
                'assignment' => $assignment,
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking training as complete: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error marking training as complete.',
            ], 500);
        }
    }
    
    /**
     * Remove the specified assignment.
     *
     * @param  \App\Models\DriverTraining  $assignment
     * @return \Illuminate\Http\Response
     */
    public function destroy(DriverTraining $assignment)
    {   
        try {
            $assignment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Training assignment deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting training assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting training assignment.',
            ], 500);
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
