<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\VehicleDriverAssignment;
use App\Models\CompanyDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $vehicle;
    protected $user;
    protected $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->vehicle = Vehicle::factory()->create();
        $this->user = User::factory()->create();
        $this->assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'user_id' => $this->user->id,
            'assignment_type' => 'company_driver',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function vehicle_driver_assignment_belongs_to_vehicle()
    {
        $this->assertInstanceOf(Vehicle::class, $this->assignment->vehicle);
        $this->assertEquals($this->vehicle->id, $this->assignment->vehicle->id);
        $this->assertEquals($this->vehicle->unit_number, $this->assignment->vehicle->unit_number);
    }

    /** @test */
    public function vehicle_driver_assignment_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->assignment->user);
        $this->assertEquals($this->user->id, $this->assignment->user->id);
        $this->assertEquals($this->user->name, $this->assignment->user->name);
    }

    /** @test */
    public function vehicle_has_many_driver_assignments()
    {
        // Create additional assignments for the same vehicle
        $assignment2 = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'assignment_type' => 'owner_operator',
            'status' => 'terminated'
        ]);
        
        $assignment3 = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'assignment_type' => 'third_party',
            'status' => 'terminated'
        ]);

        $assignments = $this->vehicle->driverAssignments;
        
        $this->assertCount(3, $assignments);
        $this->assertTrue($assignments->contains($this->assignment));
        $this->assertTrue($assignments->contains($assignment2));
        $this->assertTrue($assignments->contains($assignment3));
    }

    /** @test */
    public function vehicle_has_current_driver_assignment_relationship()
    {
        $currentAssignment = $this->vehicle->currentDriverAssignment;
        
        $this->assertInstanceOf(VehicleDriverAssignment::class, $currentAssignment);
        $this->assertEquals($this->assignment->id, $currentAssignment->id);
        $this->assertEquals('active', $currentAssignment->status);
    }

    /** @test */
    public function user_has_many_driver_assignments()
    {
        // Create additional assignments for the same user with different vehicles
        $vehicle2 = Vehicle::factory()->create();
        $assignment2 = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'user_id' => $this->user->id,
            'assignment_type' => 'owner_operator',
            'status' => 'terminated'
        ]);

        $assignments = $this->user->driverAssignments;
        
        $this->assertCount(2, $assignments);
        $this->assertTrue($assignments->contains($this->assignment));
        $this->assertTrue($assignments->contains($assignment2));
    }

    /** @test */
    public function user_has_current_driver_assignment_relationship()
    {
        $currentAssignment = $this->user->currentDriverAssignment;
        
        $this->assertInstanceOf(VehicleDriverAssignment::class, $currentAssignment);
        $this->assertEquals($this->assignment->id, $currentAssignment->id);
        $this->assertEquals('active', $currentAssignment->status);
    }

    /** @test */
    public function vehicle_driver_assignment_has_one_company_driver_detail()
    {
        $companyDetail = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $this->assignment->id,
            'employee_id' => 'EMP001',
            'department' => 'Transportation'
        ]);

        $this->assertInstanceOf(CompanyDriverDetail::class, $this->assignment->companyDriverDetail);
        $this->assertEquals($companyDetail->id, $this->assignment->companyDriverDetail->id);
        $this->assertEquals('EMP001', $this->assignment->companyDriverDetail->employee_id);
    }

    /** @test */
    public function vehicle_driver_assignment_has_one_owner_operator_detail()
    {
        $ownerOperatorDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $this->assignment->id,
            'owner_name' => 'John Owner',
            'owner_phone' => '555-0123'
        ]);

        $this->assertInstanceOf(OwnerOperatorDetail::class, $this->assignment->ownerOperatorDetail);
        $this->assertEquals($ownerOperatorDetail->id, $this->assignment->ownerOperatorDetail->id);
        $this->assertEquals('John Owner', $this->assignment->ownerOperatorDetail->owner_name);
    }

    /** @test */
    public function vehicle_driver_assignment_has_one_third_party_detail()
    {
        $thirdPartyDetail = ThirdPartyDetail::factory()->create([
            'assignment_id' => $this->assignment->id,
            'third_party_name' => 'ABC Transport',
            'third_party_phone' => '555-0123'
        ]);

        $this->assertInstanceOf(ThirdPartyDetail::class, $this->assignment->thirdPartyDetail);
        $this->assertEquals($thirdPartyDetail->id, $this->assignment->thirdPartyDetail->id);
        $this->assertEquals('ABC Transport', $this->assignment->thirdPartyDetail->third_party_name);
    }

    /** @test */
    public function company_driver_detail_belongs_to_vehicle_driver_assignment()
    {
        $companyDetail = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $this->assignment->id
        ]);

        $this->assertInstanceOf(VehicleDriverAssignment::class, $companyDetail->assignment);
        $this->assertEquals($this->assignment->id, $companyDetail->assignment->id);
    }

    /** @test */
    public function owner_operator_detail_belongs_to_vehicle_driver_assignment()
    {
        $ownerOperatorDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $this->assignment->id
        ]);

        $this->assertInstanceOf(VehicleDriverAssignment::class, $ownerOperatorDetail->assignment);
        $this->assertEquals($this->assignment->id, $ownerOperatorDetail->assignment->id);
    }

    /** @test */
    public function third_party_detail_belongs_to_vehicle_driver_assignment()
    {
        $thirdPartyDetail = ThirdPartyDetail::factory()->create([
            'assignment_id' => $this->assignment->id
        ]);

        $this->assertInstanceOf(VehicleDriverAssignment::class, $thirdPartyDetail->assignment);
        $this->assertEquals($this->assignment->id, $thirdPartyDetail->assignment->id);
    }

    /** @test */
    public function assignment_can_get_driver_details_based_on_type()
    {
        // Test company driver
        $companyAssignment = VehicleDriverAssignment::factory()->companyDriver()->create();
        $companyDetail = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $companyAssignment->id,
            'employee_id' => 'EMP001'
        ]);
        
        $details = $companyAssignment->getDriverDetails();
        $this->assertInstanceOf(CompanyDriverDetail::class, $details);
        $this->assertEquals('EMP001', $details->employee_id);

        // Test owner operator
        $ownerAssignment = VehicleDriverAssignment::factory()->ownerOperator()->create();
        $ownerDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $ownerAssignment->id,
            'owner_name' => 'John Owner'
        ]);
        
        $details = $ownerAssignment->getDriverDetails();
        $this->assertInstanceOf(OwnerOperatorDetail::class, $details);
        $this->assertEquals('John Owner', $details->owner_name);

        // Test third party
        $thirdPartyAssignment = VehicleDriverAssignment::factory()->thirdParty()->create();
        $thirdPartyDetail = ThirdPartyDetail::factory()->create([
            'assignment_id' => $thirdPartyAssignment->id,
            'third_party_name' => 'ABC Transport'
        ]);
        
        $details = $thirdPartyAssignment->getDriverDetails();
        $this->assertInstanceOf(ThirdPartyDetail::class, $details);
        $this->assertEquals('ABC Transport', $details->third_party_name);
    }

    /** @test */
    public function cascade_delete_works_correctly()
    {
        // Create details for the assignment
        $companyDetail = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $this->assignment->id
        ]);
        
        $ownerDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $this->assignment->id
        ]);
        
        $thirdPartyDetail = ThirdPartyDetail::factory()->create([
            'assignment_id' => $this->assignment->id
        ]);

        // Verify details exist
        $this->assertDatabaseHas('company_driver_details', ['id' => $companyDetail->id]);
        $this->assertDatabaseHas('owner_operator_details', ['id' => $ownerDetail->id]);
        $this->assertDatabaseHas('third_party_details', ['id' => $thirdPartyDetail->id]);

        // Delete the assignment
        $this->assignment->delete();

        // Verify details are also deleted (cascade)
        $this->assertDatabaseMissing('company_driver_details', ['id' => $companyDetail->id]);
        $this->assertDatabaseMissing('owner_operator_details', ['id' => $ownerDetail->id]);
        $this->assertDatabaseMissing('third_party_details', ['id' => $thirdPartyDetail->id]);
    }

    /** @test */
    public function eager_loading_works_correctly()
    {
        // Create details
        CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $this->assignment->id
        ]);

        // Test eager loading of vehicle and user
        $assignments = VehicleDriverAssignment::with(['vehicle', 'user', 'companyDriverDetail'])->get();
        
        $this->assertCount(1, $assignments);
        $assignment = $assignments->first();
        
        // These should not trigger additional queries
        $this->assertInstanceOf(Vehicle::class, $assignment->vehicle);
        $this->assertInstanceOf(User::class, $assignment->user);
        $this->assertInstanceOf(CompanyDriverDetail::class, $assignment->companyDriverDetail);
    }

    /** @test */
    public function scopes_work_with_relationships()
    {
        // Create terminated assignment
        $terminatedAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $this->vehicle->id,
            'status' => 'terminated',
            'termination_date' => now()->subDays(5)
        ]);

        // Test active scope
        $activeAssignments = $this->vehicle->driverAssignments()->active()->get();
        $this->assertCount(1, $activeAssignments);
        $this->assertEquals($this->assignment->id, $activeAssignments->first()->id);

        // Test all assignments
        $allAssignments = $this->vehicle->driverAssignments()->get();
        $this->assertCount(2, $allAssignments);
    }

    /** @test */
    public function polymorphic_like_behavior_works_for_driver_details()
    {
        // Create different types of assignments with their details
        $companyAssignment = VehicleDriverAssignment::factory()->companyDriver()->create();
        CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $companyAssignment->id,
            'employee_id' => 'EMP001'
        ]);

        $ownerAssignment = VehicleDriverAssignment::factory()->ownerOperator()->create();
        OwnerOperatorDetail::factory()->create([
            'assignment_id' => $ownerAssignment->id,
            'owner_name' => 'John Owner'
        ]);

        $thirdPartyAssignment = VehicleDriverAssignment::factory()->thirdParty()->create();
        ThirdPartyDetail::factory()->create([
            'assignment_id' => $thirdPartyAssignment->id,
            'third_party_name' => 'ABC Transport'
        ]);

        // Test that each assignment can access its specific details
        $this->assertEquals('EMP001', $companyAssignment->getDriverDetails()->employee_id);
        $this->assertEquals('John Owner', $ownerAssignment->getDriverDetails()->owner_name);
        $this->assertEquals('ABC Transport', $thirdPartyAssignment->getDriverDetails()->third_party_name);
    }
}