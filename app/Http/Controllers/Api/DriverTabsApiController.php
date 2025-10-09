<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\Admin\Driver\DriverAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverTabsApiController extends Controller
{
    /**
     * Save personal information tab data
     */
    public function savePersonalInfo(Request $request, Carrier $carrier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $request->get('user_id'),
                'phone' => 'required|string|max:15',
                'date_of_birth' => [
                    'required',
                    'date',
                    'before_or_equal:' . \Carbon\Carbon::now()->subYears(18)->format('Y-m-d'),
                ],
                'password' => 'nullable|min:8|confirmed',
                'password_confirmation' => 'nullable|same:password',
                'photo' => 'nullable|image|max:10240',
                'middle_name' => 'nullable|string|max:255',
                'terms_accepted' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $userId = $request->get('user_id');
            $driverDetailId = $request->get('driver_detail_id');

            // Create or update user
            if ($userId) {
                $user = User::find($userId);
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
                
                if ($request->filled('password')) {
                    $user->update(['password' => Hash::make($request->password)]);
                }
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password ?? Str::random(12)),
                    'status' => 1,
                ]);
                $user->assignRole('user_driver');
            }

            // Create or update driver detail
            if ($driverDetailId) {
                $userDriverDetail = UserDriverDetail::find($driverDetailId);
                $userDriverDetail->update([
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'date_of_birth' => $request->date_of_birth,
                    'terms_accepted' => $request->boolean('terms_accepted', false),
                ]);
            } else {
                $userDriverDetail = UserDriverDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'date_of_birth' => $request->date_of_birth,
                    'status' => 1,
                    'terms_accepted' => $request->boolean('terms_accepted', false),
                    'confirmation_token' => Str::random(60),
                    'current_step' => 1,
                ]);
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Clear existing photos
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                
                $fileName = strtolower(str_replace(' ', '_', $request->name)) . '.webp';
                $userDriverDetail->addMedia($request->file('photo')->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Personal information saved successfully',
                'data' => [
                    'user_id' => $user->id,
                    'driver_detail_id' => $userDriverDetail->id,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving personal info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving personal information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save address tab data
     */
    public function saveAddress(Request $request, Carrier $carrier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_detail_id' => 'required|exists:user_driver_details,id',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:50',
                'zip_code' => 'required|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userDriverDetail = UserDriverDetail::find($request->driver_detail_id);
            
            // Update or create address
            $address = DriverAddress::updateOrCreate(
                [
                    'user_driver_detail_id' => $userDriverDetail->id,
                    'is_current' => true
                ],
                [
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully',
                'data' => $address
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving address', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save application details tab data
     */
    public function saveApplication(Request $request, Carrier $carrier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'applying_position' => 'required|string|max:255',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'nullable|boolean',
                'has_twic_card' => 'nullable|boolean',
                'how_did_hear' => 'nullable|string|max:255',
                'expected_pay' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create or update driver application
            $application = DriverApplication::updateOrCreate(
                ['user_id' => $request->user_id],
                ['status' => 'draft']
            );

            // Create or update application details
            $applicationDetail = DriverApplicationDetail::updateOrCreate(
                ['driver_application_id' => $application->id],
                [
                    'applying_position' => $request->applying_position,
                    'applying_location' => $request->applying_location,
                    'eligible_to_work' => $request->boolean('eligible_to_work', true),
                    'can_speak_english' => $request->boolean('can_speak_english', true),
                    'has_twic_card' => $request->boolean('has_twic_card', false),
                    'how_did_hear' => $request->how_did_hear ?? 'other',
                    'expected_pay' => $request->expected_pay ?? 0.00,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application details saved successfully',
                'data' => [
                    'application_id' => $application->id,
                    'application_detail_id' => $applicationDetail->id,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving application details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving application details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver data for editing
     */
    public function getDriverData(Carrier $carrier, $driverId = null)
    {
        try {
            if (!$driverId) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            $userDriverDetail = UserDriverDetail::with([
                'user',
                'application.addresses' => function($query) {
                    $query->where('primary', true);
                },
                'user.driverApplication.applicationDetail'
            ])->find($driverId);

            if (!$userDriverDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            $data = [
                'user_id' => $userDriverDetail->user->id,
                'driver_detail_id' => $userDriverDetail->id,
                'name' => $userDriverDetail->user->name,
                'email' => $userDriverDetail->user->email,
                'middle_name' => $userDriverDetail->middle_name,
                'last_name' => $userDriverDetail->last_name,
                'phone' => $userDriverDetail->phone,
                'date_of_birth' => $userDriverDetail->date_of_birth,
                'terms_accepted' => $userDriverDetail->terms_accepted,
                'photo_url' => $userDriverDetail->getFirstMediaUrl('profile_photo_driver'),
            ];

            // Add address data
            if ($userDriverDetail->application && $userDriverDetail->application->addresses->isNotEmpty()) {
                $address = $userDriverDetail->application->addresses->first();
                $data['address'] = $address->address;
                $data['city'] = $address->city;
                $data['state'] = $address->state;
                $data['zip_code'] = $address->zip_code;
            }

            // Add application data
            if ($userDriverDetail->user->driverApplication && $userDriverDetail->user->driverApplication->applicationDetail) {
                $appDetail = $userDriverDetail->user->driverApplication->applicationDetail;
                $data['applying_position'] = $appDetail->applying_position;
                $data['applying_location'] = $appDetail->applying_location;
                $data['eligible_to_work'] = $appDetail->eligible_to_work;
                $data['can_speak_english'] = $appDetail->can_speak_english;
                $data['has_twic_card'] = $appDetail->has_twic_card;
                $data['how_did_hear'] = $appDetail->how_did_hear;
                $data['expected_pay'] = $appDetail->expected_pay;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting driver data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error getting driver data: ' . $e->getMessage()
            ], 500);
        }
    }
}