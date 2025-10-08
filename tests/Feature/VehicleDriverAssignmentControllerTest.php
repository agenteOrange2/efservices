<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\VehicleDriverAssignment;
use App\Models\CompanyDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Driver\DriverApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;

class VehicleDriverAssignmentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $vehicle;
    protected $driver;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
        $this->vehicle = Vehicle::factory()->create();
        $this->driver = User::factory()->create();
    }

    /** @test */
    public function it_can_create_company_driver_assignment()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->toDateString(),
            'notes' => 'Test company driver assignment',
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'supervisor_name' => 'John Supervisor',
            'supervisor_phone' => '555-0123',
            'salary_type' => 'hourly',
            'base_rate' => 25.50,
            'overtime_rate' => 38.25,
            'benefits_eligible' => true
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'status' => 'active'
        ]);

        $assignment = VehicleDriverAssignment::where('vehicle_id', $this->vehicle->id)->first();
        $this->assertDatabaseHas('company_driver_details', [
            'assignment_id' => $assignment->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'salary_type' => 'hourly',
            'base_rate' => 25.50
        ]);
    }

    /** @test */
    public function it_can_create_owner_operator_assignment()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'owner_operator',
            'effective_date' => now()->toDateString(),
            'notes' => 'Test owner operator assignment',
            'owner_name' => 'John Owner',
            'owner_phone' => '555-0123',
            'owner_email' => 'john@owner.com',
            'contract_agreed' => true
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'owner_operator',
            'status' => 'active'
        ]);

        $assignment = VehicleDriverAssignment::where('vehicle_id', $this->vehicle->id)->first();
        $this->assertDatabaseHas('owner_operator_details', [
            'assignment_id' => $assignment->id,
            'owner_name' => 'John Owner',
            'owner_phone' => '555-0123',
            'owner_email' => 'john@owner.com',
            'contract_agreed' => true
        ]);
    }

    /** @test */
    public function it_can_create_third_party_assignment()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'third_party',
            'effective_date' => now()->toDateString(),
            'notes' => 'Test third party assignment',
            'third_party_name' => 'ABC Transport',
            'third_party_phone' => '555-0123',
            'third_party_email' => 'contact@abc.com',
            'third_party_dba' => 'ABC Logistics',
            'third_party_address' => '123 Main St, City, State',
            'third_party_contact' => 'Jane Contact',
            'third_party_fein' => '12-3456789'
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'third_party',
            'status' => 'active'
        ]);

        $assignment = VehicleDriverAssignment::where('vehicle_id', $this->vehicle->id)->first();
        $this->assertDatabaseHas('third_party_details', [
            'assignment_id' => $assignment->id,
            'third_party_name' => 'ABC Transport',
            'third_party_phone' => '555-0123',
            'third_party_email' => 'contact@abc.com',
            'third_party_dba' => 'ABC Logistics'
        ]);
    }

    /** @test */
    public function it_terminates_existing_assignment_when_creating_new_one()
    {
        $this->actingAs($this->admin);

        // Create existing assignment
        $existingAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active'
        ]);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->toDateString()
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertRedirect();
        
        // Check that existing assignment was terminated
        $existingAssignment->refresh();
        $this->assertEquals('terminated', $existingAssignment->status);
        $this->assertNotNull($existingAssignment->termination_date);

        // Check that new assignment was created
        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_company_driver()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            // Missing effective_date
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertSessionHasErrors(['effective_date']);
    }

    /** @test */
    public function it_validates_required_fields_for_owner_operator()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'owner_operator',
            'effective_date' => now()->toDateString(),
            // Missing owner_name, owner_phone, owner_email, contract_agreed
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertSessionHasErrors([
            'owner_name',
            'owner_phone', 
            'owner_email',
            'contract_agreed'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_for_third_party()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'third_party',
            'effective_date' => now()->toDateString(),
            // Missing third_party_name, third_party_phone, third_party_email
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertSessionHasErrors([
            'third_party_name',
            'third_party_phone',
            'third_party_email'
        ]);
    }

    /** @test */
    public function it_can_terminate_assignment()
    {
        $this->actingAs($this->admin);

        $assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active'
        ]);

        $data = [
            'assignment_id' => $assignment->id,
            'termination_reason' => 'Driver resigned'
        ];

        $response = $this->delete(route('admin.vehicle-driver-assignments.destroy', $assignment), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('terminated', $assignment->status);
        $this->assertNotNull($assignment->termination_date);
        $this->assertStringContainsString('Driver resigned', $assignment->notes);
    }

    /** @test */
    public function it_can_view_assignment_history()
    {
        $this->actingAs($this->admin);

        // Create multiple assignments for the vehicle
        $assignments = VehicleDriverAssignment::factory()->count(3)->create([
            'vehicle_id' => $this->vehicle->id
        ]);

        // Test viewing assignments through the index route with vehicle filter
        $response = $this->get(route('admin.vehicle-driver-assignments.index', ['vehicle_id' => $this->vehicle->id]));

        $response->assertOk();
        $response->assertViewIs('admin.vehicle-driver-assignments.index');
        // Verify that assignments for this vehicle are displayed
        foreach ($assignments as $assignment) {
            $response->assertSee($assignment->id);
        }
    }

    /** @test */
    public function it_logs_assignment_creation()
    {
        Log::spy();
        
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->toDateString()
        ];

        $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        Log::shouldHaveReceived('info')
            ->with('Vehicle driver assignment created', \Mockery::type('array'))
            ->once();
    }

    /** @test */
    public function it_logs_assignment_termination()
    {
        Log::spy();
        
        $this->actingAs($this->admin);

        $assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'status' => 'active'
        ]);

        $data = [
            'assignment_id' => $assignment->id,
            'termination_reason' => 'Test termination'
        ];

        $this->delete(route('admin.vehicle-driver-assignments.destroy', $assignment), $data);

        Log::shouldHaveReceived('info')
            ->with('Vehicle driver assignment terminated', \Mockery::type('array'))
            ->once();
    }

    /** @test */
    public function it_handles_validation_errors_gracefully()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), [
            'vehicle_id' => 999, // Non-existent vehicle
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['vehicle_id']);
    }

    /** @test */
    public function unauthorized_users_cannot_access_assignment_endpoints()
    {
        // Crear un usuario sin permisos de admin
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('user_driver');
        
        $this->actingAs($unauthorizedUser);
        
        // El middleware CheckRoleAccess redirige usuarios no autorizados en lugar de devolver 403
        // Por lo tanto, esperamos un 302 (redirect) en lugar de 403
        
        $response = $this->get(route('admin.vehicle-driver-assignments.index'));
        $response->assertStatus(302);
        
        $response = $this->post(route('admin.vehicle-driver-assignments.store'), []);
        $response->assertStatus(302);
        
        $assignment = VehicleDriverAssignment::factory()->create();
        
        $response = $this->get(route('admin.vehicle-driver-assignments.show', $assignment));
        $response->assertStatus(302);
        
        $response = $this->put(route('admin.vehicle-driver-assignments.update', $assignment), []);
        $response->assertStatus(302);
        
        $response = $this->delete(route('admin.vehicle-driver-assignments.destroy', $assignment));
        $response->assertStatus(302);
    }

    /** @test */
    public function it_can_update_assignment_details()
    {
        $this->actingAs($this->admin);

        $assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'status' => 'active'
        ]);

        $companyDetail = CompanyDriverDetail::factory()->create([
            'assignment_id' => $assignment->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation'
        ]);

        $updateData = [
            'notes' => 'Updated assignment notes',
            'employee_id' => 'EMP002',
            'department' => 'Logistics',
            'base_rate' => 30.00
        ];

        $response = $this->put(route('admin.vehicle-driver-assignments.update', $assignment), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('Updated assignment notes', $assignment->notes);

        $companyDetail->refresh();
        $this->assertEquals('EMP002', $companyDetail->employee_id);
        $this->assertEquals('Logistics', $companyDetail->department);
        $this->assertEquals(30.00, $companyDetail->base_rate);
    }

    /** @test */
    public function it_can_show_assignment_details()
    {
        $this->actingAs($this->admin);

        $assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'owner_operator'
        ]);

        $ownerDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $assignment->id,
            'owner_name' => 'John Owner',
            'owner_phone' => '555-0123'
        ]);

        $response = $this->get(route('admin.vehicle-driver-assignments.show', $assignment));

        $response->assertOk();
        $response->assertViewIs('admin.vehicle-driver-assignments.show');
        $response->assertViewHas('assignment', $assignment);
        $response->assertSee('John Owner');
        $response->assertSee('555-0123');
    }

    /** @test */
    public function it_can_list_assignments_with_filters()
    {
        $this->actingAs($this->admin);

        // Create assignments of different types with proper relationships
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();
        $vehicle3 = Vehicle::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $companyAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'user_id' => $user1->id,
            'assignment_type' => 'company_driver',
            'status' => 'active'
        ]);

        $ownerAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'user_id' => $user2->id,
            'assignment_type' => 'owner_operator',
            'status' => 'terminated'
        ]);

        $thirdPartyAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle3->id,
            'user_id' => $user3->id,
            'assignment_type' => 'third_party',
            'status' => 'active'
        ]);

        // Test filtering by assignment type
        $response = $this->get(route('admin.vehicle-driver-assignments.index', ['type' => 'company_driver']));
        $response->assertOk();
        $response->assertViewIs('admin.vehicle-driver-assignments.index');
        $response->assertViewHas('assignments');
        
        // Check that only company driver assignments are returned
        $assignments = $response->viewData('assignments');
        $this->assertCount(1, $assignments);
        $this->assertEquals('company_driver', $assignments->first()->assignment_type);

        // Test filtering by status
        $response = $this->get(route('admin.vehicle-driver-assignments.index', ['status' => 'active']));
        $response->assertOk();
        $response->assertViewIs('admin.vehicle-driver-assignments.index');
        $response->assertViewHas('assignments');
        
        // Check that only active assignments are returned
        $assignments = $response->viewData('assignments');
        $this->assertCount(2, $assignments);
        foreach ($assignments as $assignment) {
            $this->assertEquals('active', $assignment->status);
        }
    }

    /** @test */
    public function it_maintains_driver_application_relationship()
    {
        $this->actingAs($this->admin);

        $driverApplication = DriverApplication::factory()->create([
            'user_id' => $this->driver->id
        ]);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->toDateString(),
            'driver_application_id' => $driverApplication->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation'
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment = VehicleDriverAssignment::where('vehicle_id', $this->vehicle->id)->first();
        $companyDetail = CompanyDriverDetail::where('assignment_id', $assignment->id)->first();

        $this->assertEquals($driverApplication->id, $companyDetail->driver_application_id);
        $this->assertInstanceOf(DriverApplication::class, $companyDetail->driverApplication);
    }

    /** @test */
    public function it_can_bulk_terminate_assignments()
    {
        $this->actingAs($this->admin);

        $assignments = VehicleDriverAssignment::factory()->count(3)->create([
            'status' => 'active'
        ]);

        $assignmentIds = $assignments->pluck('id')->toArray();

        $data = [
            'assignment_ids' => $assignmentIds,
            'termination_date' => now()->toDateString(),
            'termination_reason' => 'Bulk termination for restructuring'
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.bulk-terminate'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($assignments as $assignment) {
            $assignment->refresh();
            $this->assertEquals('terminated', $assignment->status);
            $this->assertNotNull($assignment->termination_date);
            $this->assertStringContainsString('Bulk termination for restructuring', $assignment->notes);
        }
    }

    /** @test */
    public function it_validates_assignment_date_constraints()
    {
        $this->actingAs($this->admin);

        $data = [
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->driver->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now()->addDays(30)->toDateString(), // Future date
            'termination_date' => now()->addDays(10)->toDateString() // Before effective date
        ];

        $response = $this->post(route('admin.vehicle-driver-assignments.store'), $data);

        $response->assertSessionHasErrors(['termination_date']);
    }
}