<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Carrier;
use App\Models\UserCarrierDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        // Create role if it doesn't exist
        if (!Role::where('name', 'user_carrier')->exists()) {
            Role::create(['name' => 'user_carrier']);
        }

        // Create user with carrier role
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 1, // Set user status to active
        ]);
        $user->assignRole('user_carrier');

        // Create carrier
        $carrier = Carrier::create([
            'name' => 'Test Carrier',
            'slug' => 'test-carrier',
            'address' => '123 Test St',
            'state' => 'CA',
            'zipcode' => '12345',
            'ein_number' => '12-3456789',
            'dot_number' => '123456',
            'mc_number' => 'MC123456',
            'id_plan' => null,
            'status' => Carrier::STATUS_ACTIVE,
        ]);

        // Create carrier details
        UserCarrierDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'phone' => '1234567890',
            'job_position' => 'Manager',
            'status' => UserCarrierDetail::STATUS_ACTIVE,
        ]);

        // Disable middleware for this test to isolate Fortify's behavior
        $this->withoutMiddleware(\App\Http\Middleware\CheckUserStatus::class);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        
        // Debug: Check actual redirect location
        echo "\nActual redirect: " . $response->headers->get('Location');
        echo "\nExpected redirect: " . route('carrier.dashboard');
        echo "\nUser roles: " . $user->fresh()->roles->pluck('name')->implode(', ');
        echo "\nCarrier details: " . ($user->fresh()->carrierDetails ? 'exists' : 'null');
        echo "\nCarrier status: " . ($user->fresh()->carrierDetails ? $user->fresh()->carrierDetails->carrier->status : 'N/A');
        echo "\nHas user_carrier role: " . ($user->fresh()->hasRole('user_carrier') ? 'yes' : 'no');
        echo "\nCarrier ID: " . ($user->fresh()->carrierDetails ? $user->fresh()->carrierDetails->carrier->id : 'N/A');
        echo "\nCarrier name: " . ($user->fresh()->carrierDetails ? $user->fresh()->carrierDetails->carrier->name : 'N/A');
        echo "\nSTATUS_ACTIVE constant: " . \App\Models\Carrier::STATUS_ACTIVE;
        echo "\nUser status: " . $user->fresh()->status;
        echo "\nUserCarrierDetail status: " . ($user->fresh()->carrierDetails ? $user->fresh()->carrierDetails->status : 'N/A');
        
        $response->assertRedirect(route('carrier.dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
