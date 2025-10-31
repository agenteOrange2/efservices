<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Carrier;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\MasterCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DriverContactMail;

class DriverTypeController extends Controller
{
    /**
     * Display a listing of drivers with carrier, company but no vehicle assignment.
     */
    public function index(Request $request)
    {
        try {
            // Nueva lógica: Drivers con carrier y company (con o sin asignación de vehículo)
            $query = UserDriverDetail::with([
                'user',
                'carrier',
                'driverEmploymentCompanies' => function($q) {
                    $q->whereNotNull('master_company_id');
                },
                'vehicleAssignments' => function($q) {
                    $q->where('status', 'active')->with('vehicle');
                }
            ])
            ->whereNotNull('carrier_id') // Tiene carrier asignado
            ->where('application_completed', 1) // Aplicación completada
            ->whereHas('user', function($q) {
                $q->where('status', 1); // Usuario activo
            })
            ->whereHas('driverEmploymentCompanies', function($q) {
                $q->whereNotNull('master_company_id'); // Ha elegido una company
            });

            // Aplicar filtros
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('user', function($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', $searchTerm)
                                 ->orWhere('email', 'like', $searchTerm);
                    });
                });
            }

            if ($request->filled('carrier_id')) {
                $query->where('carrier_id', $request->carrier_id);
            }

            if ($request->filled('company_name')) {
                $query->whereHas('driverEmploymentCompanies', function($q) use ($request) {
                    $q->whereHas('company', function($companyQuery) use ($request) {
                        $companyQuery->where('name', 'like', '%' . $request->company_name . '%');
                    });
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Ordenar resultados
            $query->orderBy('created_at', 'desc');

            $drivers = $query->paginate(15);

            // Obtener datos para filtros
            $allCarriers = Carrier::orderBy('name')->get();
            $allCompanies = MasterCompany::whereHas('driverEmploymentCompanies')
                ->orderBy('company_name')
                ->pluck('company_name', 'id');

            return view('admin.driver-types.index', compact('drivers', 'allCarriers', 'allCompanies'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@index: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Error loading drivers: ' . $e->getMessage());
        }
    }

    /**
     * Get driver types data for AJAX requests (Tabulator)
     */
    public function getData(Request $request)
    {
        return $this->getDriverTypesData($request);
    }

    /**
     * Get driver types data for AJAX requests (Tabulator)
     */
    private function getDriverTypesData(Request $request)
    {
        try {
            // Obtener solo las aplicaciones de conductores que tienen detalles asociados
            $query = DriverApplication::with([
                'details',
                'details.vehicle',
                'details.vehicle.carrier',
                'ownerOperatorDetail',
                'thirdPartyDetail'
            ])->whereHas('details'); // Solo mostrar aplicaciones que tienen detalles

            // Aplicar filtros si existen
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('ownerOperatorDetail', function($subQ) use ($search) {
                        $subQ->where('owner_name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('thirdPartyDetail', function($subQ) use ($search) {
                        $subQ->where('driver_name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
                    });
                });
            }

            $driverApplications = $query->orderBy('created_at', 'desc')->get();

            // Formatear los datos para Tabulator
            $data = $driverApplications->map(function($application) {
                // Determinar el tipo de ownership basado en los detalles existentes
                $ownershipType = 'other';
                $userDriverDetail = null;
                $ownerOperatorDetail = null;
                $thirdPartyDetail = null;

                if ($application->ownerOperatorDetail) {
                    $ownershipType = 'owner_operator';
                    $ownerOperatorDetail = $application->ownerOperatorDetail;
                } elseif ($application->thirdPartyDetail) {
                    $ownershipType = 'third_party';
                    $thirdPartyDetail = $application->thirdPartyDetail;
                }

                // Preparar información del vehículo
                $vehicle = null;
                if ($application->details && $application->details->vehicle) {
                    $vehicleData = $application->details->vehicle;
                    $vehicle = [
                        'id' => $vehicleData->id,
                        'unit_number' => $vehicleData->company_unit_number ?? 'N/A',
                        'make' => $vehicleData->make ?? '',
                        'model' => $vehicleData->model ?? '',
                        'carrier' => $vehicleData->carrier ? ['name' => $vehicleData->carrier->name] : null
                    ];
                }

                return [
                    'id' => $application->id,
                    'ownership_type' => $ownershipType,
                    'vehicle' => $vehicle,
                    'user_driver_detail' => $userDriverDetail,
                    'owner_operator_detail' => $ownerOperatorDetail,
                    'third_party_detail' => $thirdPartyDetail,
                    'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                    'actions' => $application->id
                ];
            });

            Log::info('DriverTypes AJAX: Retrieved ' . $data->count() . ' driver applications');

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getDriverTypesData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error al cargar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified driver application.
     */
    public function showApplication(DriverApplication $driverApplication)
    {
        try {
            $driverApplication->load([
                'userDriverDetail',
                'ownerOperatorDetail',
                'thirdPartyDetail',
                'details'
            ]);

            return view('admin.driver-types.show-application', compact('driverApplication'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@showApplication: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error al mostrar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified driver type.
     */
    public function edit(DriverApplication $driverApplication)
    {
        try {
            $driverApplication->load([
                'userDriverDetail',
                'ownerOperatorDetail',
                'thirdPartyDetail',
                'details'
            ]);

            // Obtener todos los vehículos para el select
            $vehicles = Vehicle::with('carrier')->orderBy('company_unit_number')->get();

            return view('admin.driver-types.edit', compact('driverApplication', 'vehicles'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@edit: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified driver type in storage.
     */
    public function update(Request $request, DriverApplication $driverApplication)
    {
        try {
            Log::info('DriverTypeController@update: Starting update for driver application ID: ' . $driverApplication->id);
            Log::info('DriverTypeController@update: Request data: ', $request->all());

            // Validación básica
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'ownership_type' => 'required|in:company_driver,owner_operator,third_party,other',
            ]);

            DB::beginTransaction();

            // Mapear ownership_type a applying_position
            $ownershipMapping = [
                'company_driver' => 'driver',
                'owner_operator' => 'owned',
                'third_party' => 'third_party_driver',
                'other' => 'other'
            ];

            // Actualizar los detalles de la aplicación
            if (!$driverApplication->details) {
                $driverApplication->details()->create([
                    'vehicle_id' => $request->vehicle_id,
                    'applying_position' => $ownershipMapping[$request->ownership_type] ?? $request->ownership_type,
                ]);
            } else {
                $driverApplication->details->update([
                    'vehicle_id' => $request->vehicle_id,
                    'applying_position' => $ownershipMapping[$request->ownership_type] ?? $request->ownership_type,
                ]);
            }

            // Actualizar detalles específicos según el tipo
            switch ($request->ownership_type) {
                case 'company_driver':
                    $this->updateCompanyDriverDetails($request, $driverApplication);
                    break;
                case 'owner_operator':
                    $this->updateOwnerOperatorDetails($request, $driverApplication);
                    break;
                case 'third_party':
                    $this->updateThirdPartyDetails($request, $driverApplication);
                    break;
                case 'other':
                    $this->updateOtherDetails($request, $driverApplication);
                    break;
            }

            DB::commit();
            Log::info('DriverTypeController@update: Successfully updated driver application ID: ' . $driverApplication->id);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Tipo de conductor actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in DriverTypeController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified driver type from storage.
     */
    public function destroy(DriverApplication $driverApplication)
    {
        try {
            DB::beginTransaction();

            // Eliminar registros relacionados
            if ($driverApplication->userDriverDetail) {
                $driverApplication->userDriverDetail->delete();
            }
            if ($driverApplication->ownerOperatorDetail) {
                $driverApplication->ownerOperatorDetail->delete();
            }
            if ($driverApplication->thirdPartyDetail) {
                $driverApplication->thirdPartyDetail->delete();
            }

            // Eliminar la aplicación principal
            $driverApplication->delete();

            DB::commit();
            Log::info('DriverTypeController@destroy: Successfully deleted driver application ID: ' . $driverApplication->id);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Tipo de conductor eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in DriverTypeController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Update company driver details
     */
    private function updateCompanyDriverDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_email' => 'nullable|email|max:255',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
        ]);

        UserDriverDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update owner operator details
     */
    private function updateOwnerOperatorDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
            'mc_number' => 'nullable|string|max:50',
            'dot_number' => 'nullable|string|max:50',
        ]);

        OwnerOperatorDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update third party details
     */
    private function updateThirdPartyDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'third_party_name' => 'required|string|max:255',
            'third_party_phone' => 'nullable|string|max:20',
            'third_party_email' => 'nullable|email|max:255',
            'third_party_address' => 'nullable|string|max:500',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
        ]);

        ThirdPartyDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update other details
     */
    private function updateOtherDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'other_details' => 'nullable|string|max:1000',
        ]);

        // Para 'other' podemos usar UserDriverDetail o crear un modelo específico
        UserDriverDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Display the specified driver details.
    **/
    public function showDriver(UserDriverDetail $driver)
    {
        try {
            $driver->load([
                'user',
                'carrier',
                'driverEmploymentCompanies.company',
                'vehicleAssignments' => function($q) {
                    $q->with('vehicle')->latest();
                },
                'inspections' => function($q) {
                    $q->orderBy('inspection_date', 'desc');
                },
                'accidents' => function($q) {
                    $q->orderBy('accident_date', 'desc');
                },
                'trafficConvictions' => function($q) {
                    $q->orderBy('conviction_date', 'desc');
                }
            ]);

            return view('admin.driver-types.show-driver', compact('driver'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@showDriver: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error loading driver details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for assigning a vehicle to the driver.
     */
    public function assignVehicle(UserDriverDetail $driver)
    {
        try {
            $driver->load(['user', 'carrier', 'activeVehicleAssignment.vehicle']);

            // Get current assignment if exists
            $currentAssignment = $driver->activeVehicleAssignment;
            
            // Get available vehicles from the same carrier (not assigned to any driver)
            $availableVehicles = \App\Models\Admin\Vehicle\Vehicle::with(['carrier'])
                ->whereDoesntHave('driverAssignments', function($q) use ($driver) {
                    $q->where('status', 'active')
                      ->where('user_driver_detail_id', '!=', $driver->id);
                })
                ->where('status', 'pending')
                ->where('carrier_id', $driver->carrier_id)
                ->orderBy('company_unit_number')
                ->orderBy('make')
                ->orderBy('model')
                ->get();

            // Log para debug
            \Log::info('Available vehicles query result', [
                'count' => $availableVehicles->count(),
                'vehicles' => $availableVehicles->pluck('id', 'make')->toArray()
            ]);

            return view('admin.driver-types.assign-vehicle', compact('driver', 'availableVehicles', 'currentAssignment'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@assignVehicle: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error loading vehicle assignment form: ' . $e->getMessage());
        }
    }

    /**
     * Store the vehicle assignment for the driver.
     */
    public function storeVehicleAssignment(Request $request, UserDriverDetail $driver)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Check if vehicle is still available
            $vehicle = \App\Models\Admin\Vehicle\Vehicle::find($request->vehicle_id);
            if ($vehicle->driverAssignments()->where('status', 'active')->exists()) {
                return redirect()->back()->with('error', 'This vehicle is already assigned to another driver.');
            }

            // Create vehicle assignment
            \App\Models\Admin\Vehicle\VehicleDriverAssignment::create([
                'user_driver_detail_id' => $driver->id,
                'vehicle_id' => $request->vehicle_id,
                'driver_type' => 'company_driver',
                'start_date' => $request->assignment_date,
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Vehicle assigned successfully to ' . $driver->user->name);
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@storeVehicleAssignment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error assigning vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for contacting the driver.
     */
    public function contact(UserDriverDetail $driver)
    {
        try {
            $driver->load(['user', 'carrier']);
            return view('admin.driver-types.contact', compact('driver'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@contact: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error loading contact form: ' . $e->getMessage());
        }
    }

    /**
     * Send contact email to the driver.
     */
    public function sendContact(Request $request, UserDriverDetail $driver)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:low,normal,high'
        ]);

        try {
            DB::transaction(function () use ($request, $driver) {
                // Create admin message record
                $adminMessage = \App\Models\AdminMessage::create([
                    'sender_id' => auth()->id(),
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'priority' => $request->priority,
                    'status' => 'sent',
                    'sent_at' => now()
                ]);

                // Create message recipient record
                \App\Models\MessageRecipient::create([
                    'message_id' => $adminMessage->id,
                    'recipient_type' => 'driver',
                    'recipient_id' => $driver->id,
                    'email' => $driver->user->email,
                    'name' => $driver->user->name,
                    'delivery_status' => 'pending'
                ]);

                // Create status log
                \App\Models\MessageStatusLog::createLog($adminMessage->id, 'sent', 'Message sent to driver via contact form');

                // Send actual email using Laravel Mail
                Mail::to($driver->user->email)->send(new DriverContactMail(
                    $request->all(),
                    auth()->user()->name ?? 'Administrator',
                    auth()->user()->email ?? config('mail.from.address')
                ));

                // Update delivery status to delivered
                $recipient = \App\Models\MessageRecipient::where('message_id', $adminMessage->id)
                    ->where('recipient_id', $driver->id)
                    ->first();
                
                if ($recipient) {
                    $recipient->markAsDelivered();
                }

                // Log for debugging
                Log::info('Contact email sent to driver and stored in database', [
                    'message_id' => $adminMessage->id,
                    'driver_id' => $driver->id,
                    'driver_email' => $driver->user->email,
                    'subject' => $request->subject,
                    'priority' => $request->priority,
                    'sent_by' => auth()->user()->name
                ]);
            });

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Message sent successfully to ' . $driver->user->name);
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@sendContact: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error sending message: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing vehicle assignment
     */
    public function editAssignment(UserDriverDetail $driver)
    {
        try {
            $driver->load(['activeVehicleAssignment.vehicle', 'carrier']);
            
            if (!$driver->activeVehicleAssignment) {
                return redirect()->route('admin.driver-types.show', $driver)
                    ->with('error', 'No active vehicle assignment found for this driver.');
            }

            $availableVehicles = Vehicle::with(['carrier'])
                ->whereDoesntHave('driverAssignments', function($q) use ($driver) {
                    $q->where('status', 'active')
                      ->where('user_driver_detail_id', '!=', $driver->id);
                })
                ->where('carrier_id', $driver->carrier_id)
                ->where('status', 'pending')
                ->orderBy('company_unit_number')
                ->get();

            return view('admin.driver-types.edit-assignment', compact('driver', 'availableVehicles'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@editAssignment: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')
                ->with('error', 'Error loading assignment edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update vehicle assignment
     */
    public function updateAssignment(Request $request, UserDriverDetail $driver)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'termination_reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $driver) {
                // Terminate current assignment
                $currentAssignment = $driver->activeVehicleAssignment;
                if ($currentAssignment) {
                    $currentAssignment->update([
                        'status' => 'inactive',
                        'end_date' => $request->effective_date,
                        'notes' => $currentAssignment->notes . "\n\nReason: " . ($request->termination_reason ?? 'Vehicle change')
                    ]);
                }

                // Create new assignment
                \App\Models\Admin\Vehicle\VehicleDriverAssignment::create([
                    'user_driver_detail_id' => $driver->id,
                    'vehicle_id' => $request->vehicle_id,
                    'driver_type' => 'company_driver',
                    'status' => 'active',
                    'start_date' => $request->effective_date,
                    'assigned_by' => auth()->id(),
                    'notes' => $request->notes
                ]);
            });

            return redirect()->route('admin.driver-types.show', $driver)
                ->with('success', 'Vehicle assignment updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@updateAssignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error updating assignment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel current vehicle assignment
     */
    public function cancelAssignment(Request $request, UserDriverDetail $driver)
    {
        $request->validate([
            'termination_date' => 'required|date',
            'termination_reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $currentAssignment = $driver->activeVehicleAssignment;

            if (!$currentAssignment) {
                return redirect()->back()->with('error', 'No active assignment found to cancel.');
            }

            $currentAssignment->update([
                'status' => 'inactive',
                'end_date' => $request->termination_date,
                'notes' => $currentAssignment->notes . "\n\nCancellation: " . $request->notes
            ]);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Vehicle assignment cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@cancelAssignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error cancelling assignment: ' . $e->getMessage());
        }
    }

    /**
     * Show assignment history for driver
     */
    public function assignmentHistory(UserDriverDetail $driver)
    {
        try {
            $driver->load([
                'user',
                'carrier',
                'vehicleAssignments' => function($q) {
                    $q->with(['vehicle', 'assignedByUser'])->orderBy('created_at', 'desc');
                }
            ]);

            return view('admin.driver-types.assignment-history', compact('driver'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@assignmentHistory: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')
                ->with('error', 'Error loading assignment history: ' . $e->getMessage());
        }
    }
}