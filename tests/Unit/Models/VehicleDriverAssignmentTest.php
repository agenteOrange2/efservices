<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use App\Models\CompanyDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class VehicleDriverAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        $assignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle->id
        ]);

        $this->assertInstanceOf(Vehicle::class, $assignment->vehicle);
        $this->assertEquals($vehicle->id, $assignment->vehicle->id);
    }

    /** @test */
    public function it_can_have_company_driver_details()
    {
        $assignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'company_driver'
        ]);
        
        $companyDetail = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $assignment->id
        ]);

        $this->assertInstanceOf(CompanyDriverDetail::class, $assignment->companyDriverDetail);
        $this->assertEquals($companyDetail->id, $assignment->companyDriverDetail->id);
    }

    /** @test */
    public function it_can_have_owner_operator_details()
    {
        $assignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'owner_operator'
        ]);
        
        $ownerDetail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $assignment->id
        ]);

        $this->assertInstanceOf(OwnerOperatorDetail::class, $assignment->ownerOperatorDetail);
        $this->assertEquals($ownerDetail->id, $assignment->ownerOperatorDetail->id);
    }

    /** @test */
    public function it_can_have_third_party_details()
    {
        $assignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'third_party'
        ]);
        
        $thirdParty = ThirdPartyDetail::factory()->create([
            'assignment_id' => $assignment->id
        ]);

        $this->assertInstanceOf(ThirdPartyDetail::class, $assignment->thirdPartyDetail);
        $this->assertEquals($thirdParty->id, $assignment->thirdPartyDetail->id);
    }

    /** @test */
    public function it_can_check_if_assignment_is_active()
    {
        $activeAssignment = VehicleDriverAssignment::factory()->create([
            'status' => 'active'
        ]);
        
        $inactiveAssignment = VehicleDriverAssignment::factory()->create([
            'status' => 'terminated'
        ]);

        $this->assertEquals('active', $activeAssignment->status);
        $this->assertEquals('terminated', $inactiveAssignment->status);
    }

    /** @test */
    public function it_can_get_driver_details_based_on_type()
    {
        // Company driver
        $companyAssignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'company_driver'
        ]);
        $companyDriver = CompanyDriverDetail::factory()->create([
            'vehicle_driver_assignment_id' => $companyAssignment->id
        ]);

        $this->assertEquals($companyDriver->id, $companyAssignment->getDriverDetails()->id);

        // Owner operator
        $ownerAssignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'owner_operator'
        ]);
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $ownerAssignment->id
        ]);

        $this->assertEquals($ownerOperator->id, $ownerAssignment->getDriverDetails()->id);

        // Third party
        $thirdPartyAssignment = VehicleDriverAssignment::factory()->create([
            'assignment_type' => 'third_party'
        ]);
        $thirdParty = ThirdPartyDetail::factory()->create([
            'assignment_id' => $thirdPartyAssignment->id
        ]);

        $this->assertEquals($thirdParty->id, $thirdPartyAssignment->getDriverDetails()->id);
    }

    /** @test */
    public function it_validates_driver_type_enum()
    {
        $validTypes = ['company_driver', 'owner_operator', 'third_party'];
        
        foreach ($validTypes as $type) {
            $assignment = VehicleDriverAssignment::factory()->create([
                'assignment_type' => $type
            ]);
            
            $this->assertEquals($type, $assignment->assignment_type);
        }
    }

    /** @test */
    public function it_can_scope_active_assignments()
    {
        VehicleDriverAssignment::factory()->count(3)->create(['status' => 'active']);
        VehicleDriverAssignment::factory()->count(2)->create(['status' => 'terminated']);

        $activeAssignments = VehicleDriverAssignment::active()->get();
        
        $this->assertCount(3, $activeAssignments);
        $activeAssignments->each(function ($assignment) {
            $this->assertEquals('active', $assignment->status);
        });
    }

    /** @test */
    public function it_can_scope_by_driver_type()
    {
        VehicleDriverAssignment::factory()->count(2)->create(['assignment_type' => 'company_driver']);
        VehicleDriverAssignment::factory()->count(3)->create(['assignment_type' => 'owner_operator']);
        VehicleDriverAssignment::factory()->count(1)->create(['assignment_type' => 'third_party']);

        $companyDrivers = VehicleDriverAssignment::byAssignmentType('company_driver')->get();
        $ownerOperators = VehicleDriverAssignment::byAssignmentType('owner_operator')->get();
        $thirdParty = VehicleDriverAssignment::byAssignmentType('third_party')->get();
        
        $this->assertCount(2, $companyDrivers);
        $this->assertCount(3, $ownerOperators);
        $this->assertCount(1, $thirdParty);
    }

    /** @test */
    public function it_has_default_values()
    {
        $assignment = VehicleDriverAssignment::factory()->create();

        $this->assertNotNull($assignment->assigned_at);
        $this->assertNotNull($assignment->effective_date);
    }

    /** @test */
    public function it_can_end_assignment()
    {
        $assignment = VehicleDriverAssignment::factory()->create(['status' => 'active']);

        $assignment->update([
            'status' => 'terminated',
            'termination_date' => now()->toDateString()
        ]);

        $this->assertEquals('terminated', $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->termination_date);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $assignment = VehicleDriverAssignment::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $assignment->user);
        $this->assertEquals($user->id, $assignment->user->id);
    }

    /** @test */
    public function it_can_have_notes()
    {
        $notes = 'This is a test note for the assignment';
        $assignment = VehicleDriverAssignment::factory()->create([
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $assignment->notes);
    }

    /** @test */
    public function it_can_have_assigned_by_user()
    {
        $assignedByUser = User::factory()->create();
        $assignment = VehicleDriverAssignment::factory()->create([
            'assigned_by' => $assignedByUser->id
        ]);

        $this->assertInstanceOf(User::class, $assignment->assignedBy);
        $this->assertEquals($assignedByUser->id, $assignment->assignedBy->id);
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $assignment = VehicleDriverAssignment::factory()->create([
            'effective_date' => '2024-01-15',
            'termination_date' => '2024-12-31'
        ]);

        $this->assertInstanceOf(Carbon::class, $assignment->effective_date);
        $this->assertInstanceOf(Carbon::class, $assignment->termination_date);
        $this->assertEquals('2024-01-15', $assignment->effective_date->toDateString());
        $this->assertEquals('2024-12-31', $assignment->termination_date->toDateString());
    }

    /** @test */
    public function it_can_scope_by_vehicle()
    {
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();
        
        VehicleDriverAssignment::factory()->count(2)->create(['vehicle_id' => $vehicle1->id]);
        VehicleDriverAssignment::factory()->count(3)->create(['vehicle_id' => $vehicle2->id]);

        $vehicle1Assignments = VehicleDriverAssignment::forVehicle($vehicle1->id)->get();
        $vehicle2Assignments = VehicleDriverAssignment::forVehicle($vehicle2->id)->get();
        
        $this->assertCount(2, $vehicle1Assignments);
        $this->assertCount(3, $vehicle2Assignments);
        
        $vehicle1Assignments->each(function ($assignment) use ($vehicle1) {
            $this->assertEquals($vehicle1->id, $assignment->vehicle_id);
        });
    }

    /** @test */
    public function it_can_scope_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        VehicleDriverAssignment::factory()->count(2)->create(['user_id' => $user1->id]);
        VehicleDriverAssignment::factory()->count(1)->create(['user_id' => $user2->id]);

        $user1Assignments = VehicleDriverAssignment::forUser($user1->id)->get();
        $user2Assignments = VehicleDriverAssignment::forUser($user2->id)->get();
        
        $this->assertCount(2, $user1Assignments);
        $this->assertCount(1, $user2Assignments);
        
        $user1Assignments->each(function ($assignment) use ($user1) {
            $this->assertEquals($user1->id, $assignment->user_id);
        });
    }

    /** @test */
    public function it_validates_status_enum()
    {
        $validStatuses = ['active', 'inactive', 'pending', 'terminated'];
        
        foreach ($validStatuses as $status) {
            $assignment = VehicleDriverAssignment::factory()->create([
                'status' => $status
            ]);
            
            $this->assertEquals($status, $assignment->status);
        }
    }

    /** @test */
    public function it_can_get_current_active_assignment_for_vehicle()
    {
        $vehicle = Vehicle::factory()->create();
        
        // Create terminated assignment
        VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle->id,
            'status' => 'terminated'
        ]);
        
        // Create active assignment
        $activeAssignment = VehicleDriverAssignment::factory()->create([
            'vehicle_id' => $vehicle->id,
            'status' => 'active'
        ]);
        
        $currentAssignment = VehicleDriverAssignment::currentForVehicle($vehicle->id)->first();
        
        $this->assertNotNull($currentAssignment);
        $this->assertEquals($activeAssignment->id, $currentAssignment->id);
        $this->assertEquals('active', $currentAssignment->status);
    }
}