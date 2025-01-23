<?php

namespace App\Http\Controllers\Driver;

use Carbon\Carbon;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;

class StepController extends Controller
{
    public function createStep1(Carrier $carrier)
    {
        // Obtener el driver más reciente si viene de la creación
        $driver = UserDriverDetail::where('carrier_id', $carrier->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$driver) {
            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('No driver found.');
        }

        return view('admin.user_driver.applications.step1', compact('carrier', 'driver'));
    }

    public function storeStep1(Request $request, Carrier $carrier)
    {
        Log::info('Iniciando storeStep1', [
            'request_data' => $request->all(),
            'carrier_id' => $carrier->id
        ]);

        try {
            $validated = $request->validate([
                'suffix' => 'nullable|string|max:50',
                'social_security_number' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
            ]);

            Log::info('Datos validados correctamente', [
                'validated_data' => $validated
            ]);

            DB::beginTransaction();
            Log::info('Iniciando transacción DB');

            $driver = UserDriverDetail::where('carrier_id', $carrier->id)
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('Driver encontrado', [
                'driver_id' => $driver ? $driver->id : null,
                'user_id' => $driver ? $driver->user_id : null
            ]);

            $application = DriverApplication::create([
                'user_id' => $driver->user_id,
                'carrier_id' => $carrier->id,
                'suffix' => $validated['suffix'],
                'social_security_number' => $validated['social_security_number'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => DriverApplication::STATUS_DRAFT
            ]);

            Log::info('Aplicación creada', [
                'application_id' => $application->id,
                'status' => $application->status
            ]);

            DB::commit();
            Log::info('Transacción completada exitosamente');

            // Modificamos esta parte para pasar la application en lugar del driver
            return redirect()->route('admin.carrier.user_drivers.application.step2', [
                'carrier' => $carrier,
                'application' => $application->id  // Cambiamos driver por application
            ])->with('success', 'Step 1 completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep1', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors('Error al guardar la aplicación: ' . $e->getMessage())->withInput();
        }
    }

    public function createStep2(Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando createStep2', [
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            // Obtener el driver asociado con la aplicación
            $driver = UserDriverDetail::where('user_id', $application->user_id)
                ->where('carrier_id', $carrier->id)
                ->first();

            if (!$driver) {
                Log::error('Driver no encontrado para la aplicación', [
                    'application_id' => $application->id,
                    'user_id' => $application->user_id
                ]);
                throw new \Exception('Driver no encontrado');
            }

            // Verificar si existe una dirección actual
            $currentAddress = $application->addresses()->where('to_date', null)->first();

            Log::info('Datos recuperados para Step 2', [
                'driver_id' => $driver->id,
                'has_current_address' => $currentAddress ? true : false
            ]);

            return view('admin.user_driver.applications.step2', compact(
                'carrier',
                'application',
                'driver',
                'currentAddress'
            ));
        } catch (\Exception $e) {
            Log::error('Error en createStep2', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error loading step 2: ' . $e->getMessage());
        }
    }

    public function storeStep2(Request $request, Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando storeStep2', [
            'request_data' => $request->except(['_token']),
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);
    
        try {
            $validated = $request->validate([
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:20',
                'lived_three_years' => 'required|boolean',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date|after:from_date',
                'previous_addresses' => 'required_if:lived_three_years,false|array',
                'previous_addresses.*.address_line1' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.city' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.state' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.zip_code' => 'required_if:lived_three_years,false|string|max:20',
                'previous_addresses.*.from_date' => 'required_if:lived_three_years,false|date',
                'previous_addresses.*.to_date' => 'required_if:lived_three_years,false|date|after:previous_addresses.*.from_date',
            ]);
    
            // Verificar que se cubran 3 años
            $mainFromDate = Carbon::parse($validated['from_date']);
            $mainToDate = $validated['to_date'] ? Carbon::parse($validated['to_date']) : Carbon::now();
            $totalDuration = $mainFromDate->diffInYears($mainToDate);
    
            if (!$validated['lived_three_years'] && isset($validated['previous_addresses'])) {
                foreach ($validated['previous_addresses'] as $address) {
                    $fromDate = Carbon::parse($address['from_date']);
                    $toDate = Carbon::parse($address['to_date']);
                    $totalDuration += $fromDate->diffInYears($toDate);
                }
            }
    
            if ($totalDuration < 3) {
                Log::warning('No se cubren los 3 años requeridos', [
                    'total_duration' => $totalDuration,
                    'main_address_duration' => $mainFromDate->diffInYears($mainToDate)
                ]);
                return back()
                    ->withErrors(['address_history' => 'Your address history must cover at least 3 years in total.'])
                    ->withInput();
            }
    
            DB::beginTransaction();
    
            // Crear dirección actual
            $application->addresses()->create([
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'lived_three_years' => $validated['lived_three_years'],
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date']
            ]);
    
            // Si hay direcciones previas, guardarlas
            if (!$validated['lived_three_years'] && isset($validated['previous_addresses'])) {
                foreach ($validated['previous_addresses'] as $address) {
                    $application->addresses()->create($address);
                }
            }
    
            DB::commit();
            
            Log::info('Direcciones guardadas exitosamente', [
                'application_id' => $application->id,
                'total_duration' => $totalDuration
            ]);
    
            return redirect()
                ->route('admin.carrier.user_drivers.application.step3', [
                    'carrier' => $carrier,
                    'application' => $application
                ])
                ->with('success', 'Address history saved successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep2', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Error saving address information: ' . $e->getMessage())->withInput();
        }
    }

    public function createStep3(Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando createStep3', [
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            // Obtener los detalles de la aplicación si existen
            $details = $application->details;

            Log::info('Datos recuperados', [
                'has_details' => !is_null($details),
                'application_status' => $application->status
            ]);

            return view('admin.user_driver.applications.step3', compact(
                'carrier',
                'application',
                'details'
            ));
        } catch (\Exception $e) {
            Log::error('Error en createStep3', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error loading application details: ' . $e->getMessage());
        }
    }

    public function storeStep3(Request $request, Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando storeStep3', [
            'request_data' => $request->except(['_token']),
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            $validated = $request->validate([
                'applying_position' => 'required|string|max:255',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'required|boolean',
                'has_twic_card' => 'required|boolean',
                'twic_expiration_date' => 'required_if:has_twic_card,true|nullable|date',
                'known_by_other_name' => 'required|boolean',
                'other_names' => 'required_if:known_by_other_name,true|nullable|string|max:255',
                'how_did_hear' => 'required|string|max:255',
                'referral_employee_name' => 'nullable|string|max:255',
                'expected_pay' => 'required|numeric|min:0|max:999999.99'
            ]);

            DB::beginTransaction();

            // Crear o actualizar los detalles de la aplicación
            $application->details()->updateOrCreate(
                ['driver_application_id' => $application->id],
                $validated
            );

            // Actualizar el estado de la aplicación
            $application->update(['status' => 'pending_review']);

            DB::commit();

            Log::info('Aplicación completada exitosamente', [
                'application_id' => $application->id,
                'new_status' => 'pending_review'
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Application completed successfully and is now pending review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep3', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors('Error saving application details: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function review(Carrier $carrier, DriverApplication $application)
    {
        $application->load(['addresses', 'details']);
        return view('admin.driver.applications.review', compact('carrier', 'application'));
    }

    public function show(Carrier $carrier, DriverApplication $application)
    {
        $application->load(['addresses', 'details']);
        return view('admin.driver.applications.show', compact('carrier', 'application'));
    }
}
