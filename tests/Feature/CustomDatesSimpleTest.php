<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CustomDatesSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a carrier for testing
        $this->carrier = Carrier::create([
            'name' => 'Test Carrier',
            'address' => 'Test Address',
            'state' => 'CA',
            'zipcode' => '90210',
            'ein_number' => '12-3456789',
            'status' => 1
        ]);
    }

    /** @test */
    public function it_can_create_driver_with_custom_registration_date()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Custom registration date (2 months ago)
        $customDate = Carbon::now()->subMonths(2);
        
        // Create driver detail
        $driverDetail = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => 'Test',
            'last_name' => 'Driver',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'status' => 'active',
            'terms_accepted' => true,
            'confirmation_token' => 'test-token',
            'current_step' => 1,
        ]);
        
        // Set custom created_at date
        $driverDetail->created_at = $customDate;
        $driverDetail->save();
        
        // Verify the custom date was set
        $this->assertEquals(
            $customDate->format('Y-m-d H:i:s'),
            $driverDetail->fresh()->created_at->format('Y-m-d H:i:s')
        );
    }

    /** @test */
    public function it_uses_default_created_at_when_no_custom_date()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create driver detail without custom date
        $driverDetail = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => 'Test',
            'last_name' => 'Driver',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'status' => 'active',
            'terms_accepted' => true,
            'confirmation_token' => 'test-token',
            'current_step' => 1,
        ]);
        
        // Verify it uses current timestamp (within 1 minute)
        $this->assertTrue(
            $driverDetail->created_at->diffInMinutes(now()) < 1
        );
    }

    /** @test */
    public function it_can_update_existing_driver_with_custom_date()
    {
        // Create a user and driver detail
        $user = User::factory()->create();
        
        $driverDetail = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => 'Test',
            'last_name' => 'Driver',
            'phone' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'status' => 'active',
            'terms_accepted' => true,
            'confirmation_token' => 'test-token',
            'current_step' => 1,
        ]);
        
        $originalDate = $driverDetail->created_at;
        
        // Update with custom date (3 months ago)
        $customDate = Carbon::now()->subMonths(3);
        $driverDetail->created_at = $customDate;
        $driverDetail->save();
        
        // Verify the date was updated
        $this->assertNotEquals(
            $originalDate->format('Y-m-d H:i:s'),
            $driverDetail->fresh()->created_at->format('Y-m-d H:i:s')
        );
        
        $this->assertEquals(
            $customDate->format('Y-m-d H:i:s'),
            $driverDetail->fresh()->created_at->format('Y-m-d H:i:s')
        );
    }
}