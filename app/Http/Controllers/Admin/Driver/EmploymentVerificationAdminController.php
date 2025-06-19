<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Mail\EmploymentVerification;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmploymentVerificationAdminController extends Controller
{
    /**
     * Muestra la lista de verificaciones de empleo
     */
    public function index(Request $request)
    {
        $query = DriverEmploymentCompany::query()
            ->with(['userDriverDetail.user', 'masterCompany', 'verificationTokens'])
            ->where('email_sent', true);
        
        // Filtros
        if ($request->has('status')) {
            if ($request->status === 'verified') {
                $query->where('verification_status', 'verified');
            } elseif ($request->status === 'rejected') {
                $query->where('verification_status', 'rejected');
            } elseif ($request->status === 'pending') {
                $query->whereNull('verification_status');
            }
        }
        
        if ($request->has('driver')) {
            $driverId = $request->driver;
            $query->whereHas('userDriverDetail', function($q) use ($driverId) {
                $q->where('id', $driverId);
            });
        }
        
        $employmentVerifications = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        $drivers = UserDriverDetail::with('user')
            ->whereHas('employmentCompanies', function($q) {
                $q->where('email_sent', true);
            })
            ->get();
        
        return view('admin.drivers.employment-verification.index', [
            'employmentVerifications' => $employmentVerifications,
            'drivers' => $drivers
        ]);
    }
    
    /**
     * Muestra los detalles de una verificación de empleo específica
     */
    public function show($id)
    {
        $employmentCompany = DriverEmploymentCompany::with([
            'userDriverDetail.user', 
            'masterCompany', 
            'verificationTokens',
            'media'
        ])->findOrFail($id);
        
        return view('admin.drivers.employment-verification.show', [
            'employmentCompany' => $employmentCompany
        ]);
    }
    
    /**
     * Permite reenviar un correo de verificación de empleo
     */
    public function resendVerification($id)
    {
        $employmentCompany = DriverEmploymentCompany::with(['userDriverDetail', 'masterCompany'])
            ->findOrFail($id);
        
        // Crear un nuevo token de verificación
        $token = Str::random(64);
        
        // Guardar el token en la base de datos
        $verificationToken = new EmploymentVerificationToken([
            'token' => $token,
            'driver_id' => $employmentCompany->user_driver_detail_id,
            'employment_company_id' => $employmentCompany->id,
            'email' => $employmentCompany->email,
            'expires_at' => now()->addDays(7),
        ]);
        
        $verificationToken->save();
        
        // Enviar el correo electrónico
        try {
            // Obtener el nombre de la empresa
            $companyName = $employmentCompany->masterCompany ? $employmentCompany->masterCompany->name : 'Empresa personalizada';
            
            // Obtener el nombre completo del conductor
            $driverName = $employmentCompany->userDriverDetail->user->name . ' ' . $employmentCompany->userDriverDetail->last_name;
            
            // Preparar los datos de empleo para el correo
            $employmentData = [
                'positions_held' => $employmentCompany->positions_held,
                'employed_from' => $employmentCompany->employed_from,
                'employed_to' => $employmentCompany->employed_to,
                'reason_for_leaving' => $employmentCompany->reason_for_leaving,
                'subject_to_fmcsr' => $employmentCompany->subject_to_fmcsr,
                'safety_sensitive_function' => $employmentCompany->safety_sensitive_function
            ];
            
            Mail::to($employmentCompany->email)
                ->send(new EmploymentVerification(
                    $companyName,
                    $driverName,
                    $employmentData,
                    $token,
                    $employmentCompany->user_driver_detail_id,
                    $employmentCompany->id
                ));
            
            return redirect()->route('admin.drivers.employment-verification.index')
                ->with('success', 'El correo de verificación ha sido reenviado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al reenviar correo de verificación de empleo', [
                'error' => $e->getMessage(),
                'employment_company_id' => $employmentCompany->id
            ]);
            
            return redirect()->route('admin.drivers.employment-verification.index')
                ->with('error', 'Error al reenviar el correo de verificación: ' . $e->getMessage());
        }
    }
    
    /**
     * Permite marcar manualmente una verificación como verificada
     */
    public function markAsVerified(Request $request, $id)
    {
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        
        $employmentCompany->verification_status = 'verified';
        $employmentCompany->verification_date = now();
        $employmentCompany->verification_notes = $request->notes ?? 'Verificado manualmente por administrador';
        $employmentCompany->verification_by = auth()->user()->name . ' (Admin)';
        $employmentCompany->save();
        
        return redirect()->route('admin.drivers.employment-verification.show', $id)
            ->with('success', 'La verificación de empleo ha sido marcada como verificada correctamente.');
    }
    
    /**
     * Permite marcar manualmente una verificación como rechazada
     */
    public function markAsRejected(Request $request, $id)
    {
        $employmentCompany = DriverEmploymentCompany::findOrFail($id);
        
        $employmentCompany->verification_status = 'rejected';
        $employmentCompany->verification_date = now();
        $employmentCompany->verification_notes = $request->notes ?? 'Rechazado manualmente por administrador';
        $employmentCompany->verification_by = auth()->user()->name . ' (Admin)';
        $employmentCompany->save();
        
        return redirect()->route('admin.drivers.employment-verification.show', $id)
            ->with('success', 'La verificación de empleo ha sido marcada como rechazada correctamente.');
    }
}
