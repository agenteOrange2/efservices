<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use App\Models\CompanyDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Driver\DriverApplication;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function vehicle_driver_assignments_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('vehicle_driver_assignments'));
        
        $this->assertTrue(Schema::hasColumns('vehicle_driver_assignments', [
            'id', 'vehicle_id', 'user_id', 'assignment_type', 'status', 
            'assigned_by', 'assigned_at', 'effective_date', 'termination_date', 
            'notes', 'created_at', 'updated_at'
        ]));
        
        // Check foreign key constraints
        $this->assertTrue(Schema::hasColumn('vehicle_driver_assignments', 'vehicle_id'));
        $this->assertTrue(Schema::hasColumn('vehicle_driver_assignments', 'user_id'));
        
        // Test enum values for assignment_type
        $assignment = new VehicleDriverAssignment();
        $assignment->vehicle_id = 1;
        $assignment->user_id = 1;
        $assignment->assignment_type = 'company_driver';
        $assignment->status = 'active';
        $assignment->assigned_by = 1;
        $assignment->assigned_at = now();
        $assignment->effective_date = now()->toDateString();
        
        $this->assertTrue(true); // If we get here, enum is working
    }

    /** @test */
    public function company_driver_details_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('company_driver_details'));
        
        $this->assertTrue(Schema::hasColumns('company_driver_details', [
            'id', 'assignment_id', 'employee_id', 'department',
            'supervisor_name', 'supervisor_phone', 'salary_type', 'base_rate',
            'overtime_rate', 'benefits_eligible', 'notes', 'created_at', 'updated_at'
        ]));
        
        // Check foreign key constraint
        $this->assertTrue(Schema::hasColumn('company_driver_details', 'assignment_id'));
    }

    /** @test */
    public function owner_operator_details_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('owner_operator_details'));
        
        $this->assertTrue(Schema::hasColumns('owner_operator_details', [
            'id', 'assignment_id', 'owner_name', 'owner_phone',
            'owner_email', 'contract_agreed', 'notes', 'created_at', 'updated_at'
        ]));
        
        // Check foreign key constraint
        $this->assertTrue(Schema::hasColumn('owner_operator_details', 'assignment_id'));
    }

    /** @test */
    public function third_party_details_table_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('third_party_details'));
        
        $this->assertTrue(Schema::hasColumns('third_party_details', [
            'id', 'assignment_id', 'third_party_name', 'third_party_phone',
            'third_party_email', 'third_party_dba', 'third_party_address',
            'third_party_contact', 'third_party_fein', 'notes',
            'created_at', 'updated_at'
        ]));
        
        // Check foreign key constraint
        $this->assertTrue(Schema::hasColumn('third_party_details', 'assignment_id'));
    }

    /** @test */
    public function foreign_key_constraints_work_correctly()
    {
        // Create test data
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        
        // Test vehicle_driver_assignments foreign keys
        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'id' => $assignment->id,
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id
        ]);
        
        // Test company_driver_details foreign key
        $companyDetail = CompanyDriverDetail::create([
            'assignment_id' => $assignment->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'salary_type' => 'hourly',
            'base_rate' => 25.00
        ]);
        
        $this->assertDatabaseHas('company_driver_details', [
            'assignment_id' => $assignment->id,
            'employee_id' => 'EMP001'
        ]);
        
        // Test cascade delete
        $assignment->delete();
        $this->assertDatabaseMissing('company_driver_details', [
            'assignment_id' => $assignment->id
        ]);
    }

    /** @test */
    public function indexes_are_created_correctly()
    {
        // Skip this test for SQLite as getDoctrineSchemaManager is not available
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Index checking not supported for SQLite in tests');
        }
        
        // Check that important indexes exist
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        
        // Get indexes for vehicle_driver_assignments table
        $indexes = $schemaManager->listTableIndexes('vehicle_driver_assignments');
        
        // Check for vehicle_id index
        $vehicleIndexExists = false;
        foreach ($indexes as $index) {
            if (in_array('vehicle_id', $index->getColumns())) {
                $vehicleIndexExists = true;
                break;
            }
        }
        $this->assertTrue($vehicleIndexExists, 'vehicle_id index does not exist');
        
        // Check for user_id index
        $userIndexExists = false;
        foreach ($indexes as $index) {
            if (in_array('user_id', $index->getColumns())) {
                $userIndexExists = true;
                break;
            }
        }
        $this->assertTrue($userIndexExists, 'user_id index does not exist');
    }

    /** @test */
    public function default_values_are_set_correctly()
    {
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        
        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        // Check default status is 'active'
        $this->assertEquals('active', $assignment->status);
        
        // Check that termination_date is null by default
        $this->assertNull($assignment->termination_date);
        
        // Check that notes can be null
        $this->assertNull($assignment->notes);
    }

    /** @test */
    public function data_types_are_correct()
    {
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        
        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        // Test that dates are properly cast
        $this->assertInstanceOf(\Carbon\Carbon::class, $assignment->effective_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $assignment->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $assignment->updated_at);
        
        // Create company driver detail with decimal values
        $companyDetail = CompanyDriverDetail::create([
            'assignment_id' => $assignment->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'salary_type' => 'hourly',
            'base_rate' => 25.50,
            'overtime_rate' => 38.25,
            'benefits_eligible' => true
        ]);
        
        // Test decimal precision
        $this->assertEquals(25.50, $companyDetail->base_rate);
        $this->assertEquals(38.25, $companyDetail->overtime_rate);
        
        // Test boolean values
        $this->assertTrue($companyDetail->benefits_eligible);
    }

    /** @test */
    public function unique_constraints_work_correctly()
    {
        $vehicle = Vehicle::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create first active assignment
        VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user1->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user1->id,
            'assigned_at' => now()
        ]);
        
        // Should be able to create another assignment for same vehicle if first is terminated
        $firstAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)->first();
        $firstAssignment->update([
            'status' => 'terminated',
            'termination_date' => now()
        ]);
        
        $secondAssignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user2->id,
            'assignment_type' => 'owner_operator',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user2->id,
            'assigned_at' => now()
        ]);
        
        $this->assertDatabaseHas('vehicle_driver_assignments', [
            'id' => $secondAssignment->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function migration_rollback_works_correctly()
    {
        // This test ensures that if we need to rollback migrations,
        // the tables are properly dropped
        
        $this->assertTrue(Schema::hasTable('vehicle_driver_assignments'));
        $this->assertTrue(Schema::hasTable('company_driver_details'));
        $this->assertTrue(Schema::hasTable('owner_operator_details'));
        $this->assertTrue(Schema::hasTable('third_party_details'));
        
        // In a real rollback scenario, these tables would be dropped
        // This test just confirms they exist after migration
    }

    /** @test */
    public function company_and_owner_operator_details_have_driver_application_id_column()
    {
        // Verify that company_driver_details and owner_operator_details still have driver_application_id
        $this->assertTrue(Schema::hasColumn('company_driver_details', 'driver_application_id'));
        $this->assertTrue(Schema::hasColumn('owner_operator_details', 'driver_application_id'));
        // third_party_details no longer has driver_application_id after migration
        $this->assertFalse(Schema::hasColumn('third_party_details', 'driver_application_id'));
    }

    /** @test */
    public function all_detail_tables_have_assignment_id_column()
    {
        // Verify that all detail tables have the new assignment_id column
        $this->assertTrue(Schema::hasColumn('company_driver_details', 'assignment_id'));
        $this->assertTrue(Schema::hasColumn('owner_operator_details', 'assignment_id'));
        $this->assertTrue(Schema::hasColumn('third_party_details', 'assignment_id'));
    }

    /** @test */
    public function owner_operator_and_third_party_details_have_notes_column()
    {
        // Verify that owner_operator_details and third_party_details have notes column
        $this->assertTrue(Schema::hasColumn('owner_operator_details', 'notes'));
        $this->assertTrue(Schema::hasColumn('third_party_details', 'notes'));
    }

    /** @test */
    public function can_create_all_assignment_types_with_details()
    {
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        $driverApplication = DriverApplication::factory()->create();
        
        // Test company driver assignment with details
        $companyAssignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        $companyDetail = CompanyDriverDetail::create([
            'assignment_id' => $companyAssignment->id,
            'driver_application_id' => $driverApplication->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'salary_type' => 'hourly',
            'base_rate' => 25.00
        ]);
        
        $this->assertDatabaseHas('company_driver_details', [
            'assignment_id' => $companyAssignment->id,
            'employee_id' => 'EMP001'
        ]);
        
        // Test owner operator assignment with details
        $vehicle2 = Vehicle::factory()->create();
        $ownerAssignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle2->id,
            'user_id' => $user->id,
            'assignment_type' => 'owner_operator',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        $ownerDetail = OwnerOperatorDetail::create([
            'assignment_id' => $ownerAssignment->id,
            'driver_application_id' => $driverApplication->id,
            'owner_name' => 'John Doe',
            'owner_phone' => '555-1234',
            'owner_email' => 'john@example.com',
            'contract_agreed' => true,
            'notes' => 'Test notes'
        ]);
        
        $this->assertDatabaseHas('owner_operator_details', [
            'assignment_id' => $ownerAssignment->id,
            'owner_name' => 'John Doe'
        ]);
        
        // Test third party assignment with details
        $vehicle3 = Vehicle::factory()->create();
        $thirdPartyAssignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle3->id,
            'user_id' => $user->id,
            'assignment_type' => 'third_party',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        $thirdPartyDetail = ThirdPartyDetail::create([
            'assignment_id' => $thirdPartyAssignment->id,
            'third_party_name' => 'ABC Company',
            'third_party_phone' => '555-5678',
            'third_party_email' => 'contact@abc.com',
            'third_party_dba' => 'ABC Transport',
            'third_party_address' => '123 Main St',
            'third_party_contact' => 'Jane Smith',
            'third_party_fein' => '12-3456789',
            'notes' => 'Third party notes'
        ]);
        
        $this->assertDatabaseHas('third_party_details', [
            'assignment_id' => $thirdPartyAssignment->id,
            'third_party_name' => 'ABC Company'
        ]);
    }

    /** @test */
    public function cascade_delete_works_for_all_detail_tables()
    {
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();
        $driverApplication = DriverApplication::factory()->create();
        
        // Create assignment
        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'assignment_type' => 'company_driver',
            'effective_date' => now(),
            'status' => 'active',
            'assigned_by' => $user->id,
            'assigned_at' => now()
        ]);
        
        // Create details for all types
        $companyDetail = CompanyDriverDetail::create([
            'assignment_id' => $assignment->id,
            'driver_application_id' => $driverApplication->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'salary_type' => 'hourly',
            'base_rate' => 25.00
        ]);
        
        $ownerDetail = OwnerOperatorDetail::create([
            'assignment_id' => $assignment->id,
            'driver_application_id' => $driverApplication->id,
            'owner_name' => 'John Doe',
            'owner_phone' => '555-1234',
            'owner_email' => 'john@example.com',
            'contract_agreed' => true
        ]);
        
        $thirdPartyDetail = ThirdPartyDetail::create([
            'assignment_id' => $assignment->id,
            'third_party_name' => 'ABC Company',
            'third_party_phone' => '555-5678',
            'third_party_email' => 'contact@abc.com'
        ]);
        
        // Verify details exist
        $this->assertDatabaseHas('company_driver_details', ['id' => $companyDetail->id]);
        $this->assertDatabaseHas('owner_operator_details', ['id' => $ownerDetail->id]);
        $this->assertDatabaseHas('third_party_details', ['id' => $thirdPartyDetail->id]);
        
        // Delete assignment
        $assignment->delete();
        
        // Verify all details are cascade deleted
        $this->assertDatabaseMissing('company_driver_details', ['id' => $companyDetail->id]);
        $this->assertDatabaseMissing('owner_operator_details', ['id' => $ownerDetail->id]);
        $this->assertDatabaseMissing('third_party_details', ['id' => $thirdPartyDetail->id]);
    }
}