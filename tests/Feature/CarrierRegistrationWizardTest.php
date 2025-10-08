<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Carrier;
use App\Models\UserCarrierDetail;
use App\Models\Membership;
use App\Models\DocumentType;
use App\Models\CarrierDocument;
use App\Models\CarrierBankingDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\CarrierConfirmationMail;
use Spatie\Permission\Models\Role;

class CarrierRegistrationWizardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeder de roles y permisos
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        // Crear membresías de prueba
        Membership::create([
            'name' => 'Basic Membership',
            'price' => 99.99,
            'description' => 'Basic carrier membership',
            'status' => true,
            'max_carrier' => 1,
            'max_drivers' => 10,
            'max_vehicles' => 5,
            'pricing_type' => 'plan'
        ]);
        
        Membership::create([
            'name' => 'Premium Membership',
            'price' => 199.99,
            'description' => 'Premium carrier membership',
            'status' => true,
            'max_carrier' => 1,
            'max_drivers' => 20,
            'max_vehicles' => 10,
            'pricing_type' => 'plan'
        ]);
        
        // Crear tipos de documentos requeridos
        DocumentType::create([
            'name' => 'Certificate of Insurance',
            'requirement' => true
        ]);
        
        DocumentType::create([
            'name' => 'Operating Authority',
            'requirement' => true
        ]);
        
        DocumentType::create([
            'name' => 'DOT Medical Certificate',
            'requirement' => true
        ]);
    }

    /** @test */
    public function it_displays_step_1_registration_form()
    {
        $response = $this->get(route('carrier.wizard.step1'));
        
        $response->assertStatus(200)
                 ->assertViewIs('auth.user_carrier.wizard.step1-basic-info')
                 ->assertSee('Welcome to EF Services')
                 ->assertSee('name="full_name"', false)
                 ->assertSee('name="email"', false)
                  ->assertSee('name="password"', false);
    }

    /** @test */
    public function it_processes_step_1_with_valid_data()
    {
        Event::fake();
        
        $userData = [
            'full_name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '5551234567',
            'country_code' => 'US',
            'job_position' => 'Manager',
            'terms_accepted' => true
        ];
        
        $response = $this->post(route('carrier.wizard.step1.process'), $userData);
        
        $response->assertRedirect(route('login'));
        
        // Verificar que el usuario fue creado
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'status' => 1
        ]);
        
        // Verificar que UserCarrierDetail fue creado
        $this->assertDatabaseHas('user_carrier_details', [
            'user_id' => User::where('email', 'john.doe@gmail.com')->first()->id,
            'phone' => '+15551234567',
            'job_position' => 'Manager'
        ]);
    }

    /** @test */
    public function it_validates_step_1_required_fields()
    {
        $response = $this->post(route('carrier.wizard.step1.process'), []);
        
        $response->assertSessionHasErrors([
            'full_name',
            'email',
            'password',
            'phone',
            'country_code',
            'job_position',
            'terms_accepted'
        ]);
    }

    /** @test */
    public function it_validates_unique_email_in_step_1()
    {
        // Crear usuario existente
        User::factory()->create(['email' => 'existing@example.com']);
        
        $userData = [
            'full_name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '5551234567',
            'country_code' => 'US',
            'job_position' => 'Manager',
            'terms_accepted' => true
        ];
        
        $response = $this->post(route('carrier.wizard.step1.process'), $userData);
        
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_redirects_to_login_after_successful_registration()
    {
        $userData = [
            'full_name' => 'John Doe',
            'email' => 'test' . time() . '@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '5551234567',
            'country_code' => 'US',
            'job_position' => 'Manager',
            'terms_accepted' => true
        ];
        
        $response = $this->post(route('carrier.wizard.step1.process'), $userData);
        
        $response->assertRedirect(route('login'))
                 ->assertSessionHas('success', 'Account created successfully! Please check your email to verify your account, then log in to continue with your registration.');
    }

    /** @test */
    public function it_checks_email_verification_status_via_ajax()
    {
        $user = $this->createUserWithRole();
        
        // Usuario sin verificar
        $response = $this->actingAs($user)
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->get(route('carrier.wizard.check.verification'));
        
        $response->assertJson(['verified' => false]);
        
        // Usuario verificado
        $user->markEmailAsVerified();
        
        $response = $this->actingAs($user)
                         ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                         ->get(route('carrier.wizard.check.verification'));
        
        $response->assertJson(['verified' => true]);
    }

    /** @test */
    public function it_displays_step_2_only_for_verified_users()
    {
        $user = $this->createUserWithRole();
        
        // Usuario sin verificar
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step2'));
        
        $response->assertRedirect(route('carrier.wizard.step1'))
                 ->assertSessionHasErrors(['general']);
        
        // Usuario verificado
        $user->markEmailAsVerified();
        
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step2'));
        
        $response->assertStatus(200)
                 ->assertViewIs('auth.user_carrier.wizard.step2-company-info')
                 ->assertSee('Company Information');
    }

    /** @test */
    public function it_processes_step_2_with_valid_company_data()
    {
        $user = $this->createVerifiedUser();
        
        // Crear UserCarrierDetail como lo haría processStep1
        UserCarrierDetail::create([
            'user_id' => $user->id,
            'phone' => '+1234567890',
            'job_position' => 'Owner',
            'status' => 0 // Pending
        ]);
        
        $membership = Membership::first();
        
        $companyData = [
            'carrier_name' => 'Test Transport LLC',
            'address' => '123 Main St',
            'state' => 'TX',
            'zipcode' => '12345',
            'ein_number' => '12-3456789',
            'dot_number' => '123456',
            'mc_number' => '123456',
            'membership_id' => $membership->id,
            'has_documents' => 'yes',
            'business_type' => 'llc',
            'years_in_business' => 5,
            'fleet_size' => 10
        ];
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step2.process'), $companyData);
        
        $response->assertRedirect(route('carrier.wizard.step3'));
        
        // Verificar que el carrier fue creado
        $this->assertDatabaseHas('carriers', [
            'name' => 'Test Transport LLC',
            'ein_number' => '12-3456789',
            'dot_number' => '123456',
            'mc_number' => '123456',
            'status' => 2 // STATUS_PENDING
        ]);
        
        // Verificar que UserCarrierDetail fue creado
        $this->assertDatabaseHas('user_carrier_details', [
            'user_id' => $user->id,
            'status' => 1 // STATUS_ACTIVE
        ]);
    }

    /** @test */
    public function it_validates_step_2_required_fields()
    {
        $user = $this->createVerifiedUser();
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step2.process'), []);
        
        $response->assertSessionHasErrors([
            'carrier_name',
            'address',
            'state',
            'zipcode',
            'ein_number',
            'membership_id',
            'has_documents',
            'business_type',
            'years_in_business',
            'fleet_size'
        ]);
    }

    /** @test */
    public function it_validates_unique_ein_dot_mc_in_step_2()
    {
        $user = $this->createVerifiedUser();
        $membership = Membership::first();
        
        // Crear carrier existente
        Carrier::create([
            'name' => 'Existing Carrier',
            'address' => '456 Test Ave',
            'state' => 'CA',
            'zipcode' => '54321',
            'ein_number' => '12-3456789',
            'dot_number' => '123456',
            'mc_number' => '123456',
            'status' => 1 // active
        ]);
        
        $companyData = [
            'carrier_name' => 'Test Transport LLC',
            'address' => '123 Main St',
            'state' => 'TX',
            'zipcode' => '12345',
            'ein_number' => '12-3456789', // Duplicado
            'dot_number' => '123456', // Duplicado (sin prefijo DOT)
            'mc_number' => '123456', // Duplicado (sin prefijo MC)
            'membership_id' => $membership->id,
            'has_documents' => 'yes',
            'business_type' => 'llc',
            'years_in_business' => 5,
            'fleet_size' => 10
        ];
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step2.process'), $companyData);
        
        $response->assertSessionHasErrors(['ein_number', 'dot_number', 'mc_number']);
    }

    /** @test */
    public function it_displays_step_3_membership_selection()
    {
        $user = $this->createUserWithCarrier();
        
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step3'));
        
        $response->assertStatus(200)
                 ->assertViewIs('auth.user_carrier.wizard.step3-membership')
                 ->assertSee('Choose Your Plan')
                 ->assertSee('Basic Membership')
                 ->assertSee('Premium Membership');
    }

    /** @test */
    public function it_processes_step_3_membership_selection()
    {
        // Verificar que los DocumentTypes existen antes del test
        $documentTypesCount = DocumentType::where('requirement', true)->count();
        $this->assertGreaterThan(0, $documentTypesCount, 'No document types with requirement=true found');
        
        $user = $this->createUserWithCarrier();
        $membership = Membership::first();
        
        $membershipData = [
            'membership_id' => $membership->id,
            'documents_ready' => 'no',
            'terms_accepted' => true
        ];
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step3.process'), $membershipData);
        
        $response->assertRedirect(route('carrier.wizard.step4'));
        
        // Verificar que el carrier fue actualizado
        $carrier = $user->carrierDetails->carrier;
        $this->assertEquals($membership->id, $carrier->id_plan);
        $this->assertEquals(Carrier::STATUS_PENDING, $carrier->status);
        
        // Verificar que se generaron documentos base
        $this->assertDatabaseHas('carrier_documents', [
            'carrier_id' => $carrier->id
        ]);
        
        // Note: CarrierConfirmationMail is only sent during initial registration, not in wizard step 3
    }

    /** @test */
    public function it_checks_uniqueness_via_ajax()
    {
        // Crear datos existentes
        User::factory()->create(['email' => 'existing@example.com']);
        Carrier::create([
            'name' => 'Test Carrier',
            'address' => '789 Test Blvd',
            'state' => 'FL',
            'zipcode' => '33101',
            'ein_number' => '12-3456789',
            'dot_number' => '123456',
            'mc_number' => '123456',
            'status' => 1 // active
        ]);
        
        // Test email único
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->postJson(route('carrier.wizard.check.uniqueness'), [
            'field' => 'email',
            'value' => 'new@example.com'
        ]);
        $response->assertJson(['unique' => true]);
        
        // Test email duplicado
        $response = $this->postJson(route('carrier.wizard.check.uniqueness'), [
            'field' => 'email',
            'value' => 'existing@example.com'
        ]);
        $response->assertJson(['unique' => false]);
        
        // Test DOT único
        $response = $this->postJson(route('carrier.wizard.check.uniqueness'), [
            'field' => 'dot',
            'value' => '999999'
        ]);
        $response->assertJson(['unique' => true]);
        
        // Test DOT duplicado
        $response = $this->postJson(route('carrier.wizard.check.uniqueness'), [
            'field' => 'dot',
            'value' => '123456'
        ]);
        $response->assertJson(['unique' => false]);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        // Simular error de base de datos usando datos inválidos
        $userData = [
            'first_name' => str_repeat('a', 300), // Muy largo
            'last_name' => 'Doe',
            'email' => 'invalid-email', // Email inválido
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => true
        ];
        
        $response = $this->post(route('carrier.wizard.step1.process'), $userData);
        
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_redirects_authenticated_users_appropriately()
    {
        $user = $this->createUserWithActiveCarrier();
        
        // Usuario con carrier activo debe ir al dashboard
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step1'));
        
        $response->assertRedirect(route('carrier.dashboard'));
    }

    /** @test */
    public function it_displays_step_4_banking_info()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step4'));

        // Debug: mostrar información de redirección si falla
        if ($response->getStatusCode() === 302) {
            $this->fail('Redirected to: ' . $response->headers->get('Location') . 
                       ' with session errors: ' . json_encode(session()->get('errors')));
        }

        $response->assertStatus(200)
                 ->assertViewIs('auth.user_carrier.wizard.step4-banking-info')
                 ->assertSee('Banking Information')
                 ->assertSee('Account Number')
                 ->assertSee('Account Holder Name');
    }

    /** @test */
    public function it_processes_step_4_with_valid_data()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        $bankingData = [
            'account_number' => '123456789012',
            'account_holder_name' => 'Test Transport LLC',
            'country_code' => 'US'
        ];
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step4.process'), $bankingData);
        
        $response->assertRedirect(route('carrier.pending.validation'));
        
        // Verificar que los datos bancarios se guardaron
        $carrier = $user->carrierDetails->carrier;
        $this->assertDatabaseHas('carrier_banking_details', [
            'carrier_id' => $carrier->id
        ]);
        
        // Verificar que el estado cambió a pending_validation
        $carrier->refresh();
        $this->assertEquals(Carrier::STATUS_PENDING_VALIDATION, $carrier->status);
    }

    /** @test */
    public function it_validates_required_fields_for_step_4()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step4.process'), []);
        
        $response->assertSessionHasErrors(['account_number', 'account_holder_name']);
    }

    /** @test */
    public function it_validates_us_bank_account_format()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        // Test con número de cuenta muy corto
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step4.process'), [
                             'account_number' => '123',
                             'account_holder_name' => 'Test Transport LLC',
                             'country_code' => 'US'
                         ]);
        
        $response->assertSessionHasErrors(['account_number']);
        
        // Test con número de cuenta muy largo
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step4.process'), [
                             'account_number' => '12345678901234567890',
                             'account_holder_name' => 'Test Transport LLC',
                             'country_code' => 'US'
                         ]);
        
        $response->assertSessionHasErrors(['account_number']);
    }

    /** @test */
    public function it_restricts_step_4_to_us_users_only()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        // Cambiar el país del carrier a uno que no sea US
        $user->carrierDetails->carrier->update(['country' => 'CA']); // Canadá
        
        $response = $this->actingAs($user)
                         ->get(route('carrier.wizard.step4'));
        
        $response->assertRedirect(route('carrier.dashboard'))
                 ->assertSessionHas('error', 'Banking information is only required for US carriers.');
    }

    /** @test */
    public function it_encrypts_banking_data()
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        $bankingData = [
            'account_number' => '123456789012',
            'account_holder_name' => 'Test Transport LLC',
            'country_code' => 'US'
        ];
        
        $response = $this->actingAs($user)
                         ->post(route('carrier.wizard.step4.process'), $bankingData);
        
        $response->assertRedirect(route('carrier.pending.validation'));
        
        // Verificar que los datos están encriptados en la base de datos
        $carrier = $user->carrierDetails->carrier;
        $bankingDetail = CarrierBankingDetail::where('carrier_id', $carrier->id)->first();
        
        $this->assertNotNull($bankingDetail);
        // Los datos en la base de datos deben estar encriptados (diferentes a los originales)
        $this->assertNotEquals($bankingData['account_number'], $bankingDetail->getRawOriginal('account_number'));
        $this->assertNotEquals($bankingData['account_holder_name'], $bankingDetail->getRawOriginal('account_holder_name'));
        
        // Pero al acceder a través del modelo deben estar desencriptados
        $this->assertEquals($bankingData['account_number'], $bankingDetail->account_number);
        $this->assertEquals($bankingData['account_holder_name'], $bankingDetail->account_holder_name);
    }

    /** @test */
    public function it_blocks_dashboard_access_for_pending_validation_status()
    {
        $user = $this->createUserWithPendingValidationCarrier();
        
        $response = $this->actingAs($user)
                         ->get(route('carrier.dashboard'));
        
        $response->assertRedirect(route('carrier.pending.validation'))
                 ->assertSessionHas('info', 'Your account is pending administrative validation. We will review your banking information and activate your account soon.');
    }

    // Helper methods
    private function createUserWithRole(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'status' => true
        ]);
        $user->assignRole('user_carrier');
        return $user;
    }
    
    private function createVerifiedUser(): User
    {
        $user = $this->createUserWithRole();
        $user->markEmailAsVerified();
        return $user;
    }
    
    private function createUserWithCarrier(): User
    {
        $user = $this->createVerifiedUser();
        
        $carrier = Carrier::create([
            'name' => 'Test Carrier LLC',
            'address' => '123 Test St',
            'state' => 'TX',
            'zipcode' => '12345',
            'country' => 'US',
            'ein_number' => '12-3456789',
            'dot_number' => 'DOT123456',
            'mc_number' => 'MC123456',
            'status' => 2 // pending_membership
        ]);
        
        UserCarrierDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'phone' => '+1234567890',
            'job_position' => 'Owner',
            'status' => 1 // STATUS_ACTIVE
        ]);
        
        return $user;
    }
    
    private function createUserWithActiveCarrier(): User
    {
        $user = $this->createUserWithCarrier();
        $user->carrierDetails->carrier->update(['status' => 1]); // active
        $user->update(['status' => true]); // Asegurar que el usuario esté activo
        return $user;
    }
    
    private function createUserWithCarrierStep3Completed(): User
    {
        $user = $this->createUserWithCarrier();
        $membership = Membership::first();
        
        // Completar step 3
        $user->carrierDetails->carrier->update([
            'id_plan' => $membership->id,
            'documents_ready' => 'yes',
            'terms_accepted_at' => now(),
            'status' => Carrier::STATUS_PENDING
        ]);
        
        // Asegurar que el usuario sea de US para el step 4
        $user->update(['country' => 'US']);
        
        return $user;
    }
    
    private function createUserWithPendingValidationCarrier(): User
    {
        $user = $this->createUserWithCarrierStep3Completed();
        
        // Crear datos bancarios y cambiar estado a pending_validation
        CarrierBankingDetail::create([
            'carrier_id' => $user->carrierDetails->carrier->id,
            'account_number' => '123456789012',
            'account_holder_name' => 'Test Transport LLC'
        ]);
        
        $user->carrierDetails->carrier->update([
            'status' => Carrier::STATUS_PENDING_VALIDATION
        ]);
        
        return $user;
    }
}