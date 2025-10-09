<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use App\Models\Admin\Driver\DriverAddress;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\DateHelper;

class AdminDriverApiController extends Controller
{
    /**
     * Store personal information for new driver
     */
    public function storePersonalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'carrier_id' => 'required|exists:carriers,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'driver'
            ]);

            // Create driver detail
            $driverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $request->carrier_id,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth ? DateHelper::toDatabase($request->date_of_birth) : null,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'status' => 2, // Pending
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            Log::info('Driver personal info created successfully', [
                'user_id' => $user->id,
                'driver_id' => $driverDetail->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Información personal guardada exitosamente',
                'data' => [
                    'driver_id' => $driverDetail->id,
                    'user_id' => $user->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver personal info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la información personal'
            ], 500);
        }
    }

    /**
     * Update personal information for existing driver
     */
    public function updatePersonalInfo(Request $request, $driverId)
    {
        $driver = UserDriverDetail::with('user')->findOrFail($driverId);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $driver->user->id,
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update user info
            $driver->user->update([
                'name' => $request->name,
                'email' => $request->email
            ]);

            // Update driver details
            $driver->update([
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth ? DateHelper::toDatabase($request->date_of_birth) : null,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Información personal actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating driver personal info', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información personal'
            ], 500);
        }
    }

    /**
     * Store or update address information
     */
    public function storeAddressInfo(Request $request, $driverId)
    {
        $driver = UserDriverDetail::findOrFail($driverId);

        $validator = Validator::make($request->all(), [
            'current_address.address_line1' => 'required|string|max:255',
            'current_address.city' => 'required|string|max:255',
            'current_address.state' => 'required|string|max:2',
            'current_address.zip_code' => 'required|string|max:10',
            'current_address.from_date' => 'required|date',
            'current_address.lived_3_years' => 'boolean',
            'previous_addresses' => 'array',
            'previous_addresses.*.address_line1' => 'required|string|max:255',
            'previous_addresses.*.city' => 'required|string|max:255',
            'previous_addresses.*.state' => 'required|string|max:2',
            'previous_addresses.*.zip_code' => 'required|string|max:10',
            'previous_addresses.*.from_date' => 'required|date',
            'previous_addresses.*.to_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete existing addresses
            if ($driver->application) {
                $driver->application->addresses()->delete();
            }

            // Save current address
            $currentAddress = $request->input('current_address');
            if ($driver->application) {
                $driver->application->addresses()->create([
                'address_line_1' => $currentAddress['address_line1'],
                'address_line_2' => $currentAddress['address_line2'] ?? '',
                'city' => $currentAddress['city'],
                'state' => $currentAddress['state'],
                'zip_code' => $currentAddress['zip_code'],
                'country' => 'US',
                'address_type' => 'current',
                'from_date' => DateHelper::toDatabase($currentAddress['from_date']),
                'to_date' => null,
                'lived_3_years' => $currentAddress['lived_3_years'] ?? false
                ]);
            }

            // Save previous addresses if any
            if ($request->has('previous_addresses')) {
                foreach ($request->input('previous_addresses') as $prevAddress) {
                    if ($driver->application) {
                        $driver->application->addresses()->create([
                        'address_line_1' => $prevAddress['address_line1'],
                        'address_line_2' => $prevAddress['address_line2'] ?? '',
                        'city' => $prevAddress['city'],
                        'state' => $prevAddress['state'],
                        'zip_code' => $prevAddress['zip_code'],
                        'country' => 'US',
                        'address_type' => 'previous',
                        'from_date' => DateHelper::toDatabase($prevAddress['from_date']),
                        'to_date' => DateHelper::toDatabase($prevAddress['to_date'])
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Información de dirección guardada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving driver address info', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la información de dirección'
            ], 500);
        }
    }

    /**
     * Store or update application information
     */
    public function storeApplicationInfo(Request $request, $driverId)
    {
        $driver = UserDriverDetail::findOrFail($driverId);

        $validator = Validator::make($request->all(), [
            'applying_position' => 'required|string',
            'applying_position_other' => 'nullable|string',
            'applying_location' => 'nullable|string',
            'eligible_to_work' => 'boolean',
            'can_speak_english' => 'boolean',
            'has_twic_card' => 'boolean',
            'twic_expiration_date' => 'nullable|date',
            'expected_pay' => 'nullable|string',
            'how_did_hear' => 'required|string',
            'how_did_hear_other' => 'nullable|string',
            'referral_employee_name' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $applicationData = [
                'applying_position' => $request->applying_position,
                'applying_position_other' => $request->applying_position_other,
                'applying_location' => $request->applying_location,
                'eligible_to_work' => $request->eligible_to_work ?? true,
                'can_speak_english' => $request->can_speak_english ?? true,
                'has_twic_card' => $request->has_twic_card ?? false,
                'twic_expiration_date' => $request->twic_expiration_date ? DateHelper::toDatabase($request->twic_expiration_date) : null,
                'expected_pay' => $request->expected_pay,
                'how_did_hear' => $request->how_did_hear,
                'how_did_hear_other' => $request->how_did_hear_other,
                'referral_employee_name' => $request->referral_employee_name
            ];

            // Update or create application
            if ($driver->application) {
                $driver->application->update($applicationData);
            } else {
                $driver->application()->create($applicationData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Información de aplicación guardada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving driver application info', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la información de aplicación'
            ], 500);
        }
    }

    /**
     * Get driver data for editing
     */
    public function getDriverData($driverId)
    {
        try {
            $driver = UserDriverDetail::with([
                'user',
                'application.addresses',
                'application',
                'carrier'
            ])->findOrFail($driverId);

            $data = [
                'personal' => [
                    'name' => $driver->user->name,
                    'email' => $driver->user->email,
                    'phone' => $driver->phone,
                    'date_of_birth' => $driver->date_of_birth ? DateHelper::toDisplay($driver->date_of_birth) : '',
                    'middle_name' => $driver->middle_name,
                    'last_name' => $driver->last_name
                ],
                'addresses' => [
                    'current' => null,
                    'previous' => []
                ],
                'application' => $driver->application ? [
                    'applying_position' => $driver->application->applying_position,
                    'applying_position_other' => $driver->application->applying_position_other,
                    'applying_location' => $driver->application->applying_location,
                    'eligible_to_work' => $driver->application->eligible_to_work,
                    'can_speak_english' => $driver->application->can_speak_english,
                    'has_twic_card' => $driver->application->has_twic_card,
                    'twic_expiration_date' => $driver->application->twic_expiration_date ? DateHelper::toDisplay($driver->application->twic_expiration_date) : '',
                    'expected_pay' => $driver->application->expected_pay,
                    'how_did_hear' => $driver->application->how_did_hear,
                    'how_did_hear_other' => $driver->application->how_did_hear_other,
                    'referral_employee_name' => $driver->application->referral_employee_name
                ] : null
            ];

            // Process addresses
            if ($driver->application && $driver->application->addresses) {
                foreach ($driver->application->addresses as $address) {
                $addressData = [
                    'address_line1' => $address->address_line_1,
                    'address_line2' => $address->address_line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip_code' => $address->zip_code,
                    'from_date' => $address->from_date ? DateHelper::toDisplay($address->from_date) : '',
                    'to_date' => $address->to_date ? DateHelper::toDisplay($address->to_date) : '',
                    'lived_3_years' => $address->lived_3_years ?? false
                ];

                if ($address->address_type === 'current') {
                    $data['addresses']['current'] = $addressData;
                } else {
                    $data['addresses']['previous'][] = $addressData;
                }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting driver data', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del conductor'
            ], 500);
        }
    }
}