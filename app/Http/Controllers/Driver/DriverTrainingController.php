<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverTraining;
use App\Models\Admin\Driver\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverTrainingController extends Controller
{
    /**
     * Mostrar el listado de entrenamientos asignados al conductor
     */
    public function index()
    {
        $user = Auth::user();
        $driverDetail = $user->driverDetail;
        
        if (!$driverDetail) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'No se encontró información de conductor');
        }
        
        $assignments = DriverTraining::where('user_driver_detail_id', $driverDetail->id)
            ->with(['training'])
            ->orderBy('status')
            ->orderBy('due_date')
            ->paginate(10);
            
        return view('driver.trainings.index', compact('assignments'));
    }
    
    /**
     * Mostrar un entrenamiento específico para el conductor
     */
    public function show($id)
    {
        $user = Auth::user();
        $driverDetail = $user->driverDetail;
        
        if (!$driverDetail) {
            return redirect()->route('driver.dashboard')
                ->with('error', 'No se encontró información de conductor');
        }
        
        $assignment = DriverTraining::where('id', $id)
            ->where('user_driver_detail_id', $driverDetail->id)
            ->with(['training'])
            ->firstOrFail();
            
        $training = $assignment->training;
        $media = $training->getMedia('training_files');
        
        return view('driver.trainings.show', compact('assignment', 'training', 'media'));
    }
    
    /**
     * Marcar un entrenamiento como completado
     */
    public function complete(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $driverDetail = $user->driverDetail;
            
            if (!$driverDetail) {
                return redirect()->route('driver.dashboard')
                    ->with('error', 'No se encontró información de conductor');
            }
            
            $assignment = DriverTraining::where('id', $id)
                ->where('user_driver_detail_id', $driverDetail->id)
                ->firstOrFail();
                
            // Validar que el entrenamiento no esté ya completado
            if ($assignment->status === 'completed') {
                return redirect()->route('driver.trainings.index')
                    ->with('info', 'Este entrenamiento ya ha sido completado');
            }
            
            // Marcar como completado
            $assignment->status = 'completed';
            $assignment->completed_date = now();
            $assignment->completion_notes = $request->notes;
            $assignment->save();
            
            return redirect()->route('driver.trainings.index')
                ->with('success', 'Entrenamiento marcado como completado');
                
        } catch (\Exception $e) {
            Log::error('Error al completar entrenamiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al completar entrenamiento: ' . $e->getMessage());
        }
    }
    
    /**
     * Marcar un entrenamiento como en progreso
     */
    public function startProgress($id)
    {
        try {
            $user = Auth::user();
            $driverDetail = $user->driverDetail;
            
            if (!$driverDetail) {
                return redirect()->route('driver.dashboard')
                    ->with('error', 'No se encontró información de conductor');
            }
            
            $assignment = DriverTraining::where('id', $id)
                ->where('user_driver_detail_id', $driverDetail->id)
                ->firstOrFail();
                
            // Validar que el entrenamiento no esté ya completado
            if ($assignment->status === 'completed') {
                return redirect()->route('driver.trainings.index')
                    ->with('info', 'Este entrenamiento ya ha sido completado');
            }
            
            // Marcar como en progreso
            if ($assignment->status === 'assigned' || $assignment->status === 'overdue') {
                $assignment->status = 'in_progress';
                $assignment->save();
            }
            
            return redirect()->route('driver.trainings.show', $assignment->id);
                
        } catch (\Exception $e) {
            Log::error('Error al iniciar entrenamiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al iniciar entrenamiento: ' . $e->getMessage());
        }
    }
    
    /**
     * Previsualizar o descargar un documento adjunto a un entrenamiento
     */
    public function previewDocument($id, Request $request = null)
    {
        try {
            $user = Auth::user();
            $driverDetail = $user->driverDetail;
            
            if (!$driverDetail) {
                return redirect()->route('driver.dashboard')
                    ->with('error', 'No se encontró información de conductor');
            }
            
            // Buscar el documento en la tabla media de Spatie
            $media = Media::findOrFail($id);

            // Verificar que el documento pertenece a un entrenamiento
            if ($media->model_type !== Training::class) {
                return redirect()->back()->with('error', 'Tipo de documento inválido');
            }
            
            // Verificar que el conductor tiene acceso a este entrenamiento
            $training = Training::findOrFail($media->model_id);
            $hasAccess = DriverTraining::where('training_id', $training->id)
                ->where('user_driver_detail_id', $driverDetail->id)
                ->exists();
                
            if (!$hasAccess) {
                return redirect()->back()->with('error', 'No tienes acceso a este documento');
            }

            // Determinar si es descarga o visualización
            $isDownload = $request && $request->has('download');

            if ($isDownload) {
                // Si es descarga, usar el método de descarga de Spatie
                return response()->download(
                    $media->getPath(), 
                    $media->file_name,
                    ['Content-Type' => $media->mime_type]
                );
            } else {
                // Si es visualización, usar 'inline' para mostrar en el navegador si es posible
                $headers = [
                    'Content-Type' => $media->mime_type,
                    'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
                ];
                
                return response()->file($media->getPath(), $headers);
            }
        } catch (\Exception $e) {
            Log::error('Error al previsualizar documento', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al acceder al documento: ' . $e->getMessage());
        }
    }
}
