<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Mail\ThirdPartyVehicleVerification;
use App\Models\Admin\Vehicle\VehicleVerificationToken;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ThirdPartyEmailHelper
{
    /**
     * Envía un correo electrónico de verificación al tercero
     *
     * @param array $thirdPartyData Datos del tercero
     * @param object $vehicle El vehículo
     * @param int $driverApplicationId ID de la aplicación del conductor
     * @return bool
     */
    public static function sendVerificationEmail($thirdPartyData, $vehicle, $driverApplicationId)
    {
        try {
            // Crear token de verificación
            $token = Str::random(64);
            
            // Obtener datos del driver si existe
            $driverName = '';
            $driverId = 0;
            if ($vehicle->user_driver_detail_id) {
                $driver = UserDriverDetail::with('user')->find($vehicle->user_driver_detail_id);
                if ($driver && $driver->user) {
                    $driverName = $driver->user->name;
                    $driverId = $driver->id;
                }
            }
            
            // Datos del vehículo para el correo
            $vehicleData = [
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'vin' => $vehicle->vin,
                'type' => $vehicle->type
            ];
            
            // Enviar correo usando la clase ThirdPartyVehicleVerification
            Mail::to($thirdPartyData['email'])
                ->send(new ThirdPartyVehicleVerification(
                    $thirdPartyData['name'],
                    $driverName,
                    $vehicleData,
                    $token,
                    $driverId,
                    $driverApplicationId
                ));
            
            // Guardar token en la base de datos
            VehicleVerificationToken::create([
                'token' => $token,
                'driver_application_id' => $driverApplicationId,
                'vehicle_id' => $vehicle->id,
                'third_party_name' => $thirdPartyData['name'],
                'third_party_email' => $thirdPartyData['email'],
                'third_party_phone' => $thirdPartyData['phone'],
                'verified' => false,
                'expires_at' => now()->addDays(30)
            ]);
            
            // Log del envío
            Log::info('Correo enviado a third party', [
                'vehicle_id' => $vehicle->id,
                'third_party_email' => $thirdPartyData['email']
            ]);
            
            return true;
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error al enviar correo a third party', [
                'vehicle_id' => $vehicle->id,
                'third_party_email' => $thirdPartyData['email'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
