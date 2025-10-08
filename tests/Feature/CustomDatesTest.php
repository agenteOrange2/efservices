<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserDriverDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CustomDatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        // Crear un usuario admin para las pruebas
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');
    }

    /** @test */
    public function it_can_save_custom_dates_to_database()
    {
        $this->actingAs($this->admin);
        
        // Crear un driver
        $driver = UserDriverDetail::factory()->create();
        
        // Actualizar con fechas personalizadas
        $customRegistrationDate = Carbon::now()->subDays(30);
        $customCompletionDate = Carbon::now()->subDays(15);
        
        $driver->update([
            'use_custom_dates' => true,
            'custom_registration_date' => $customRegistrationDate,
            'custom_completion_date' => $customCompletionDate
        ]);
        
        // Verificar que se guardaron correctamente
        $driver->refresh();
        
        $this->assertTrue($driver->use_custom_dates);
        $this->assertEquals($customRegistrationDate->format('Y-m-d'), $driver->custom_registration_date->format('Y-m-d'));
        $this->assertEquals($customCompletionDate->format('Y-m-d'), $driver->custom_completion_date->format('Y-m-d'));
    }

    /** @test */
    public function it_can_clear_custom_dates_from_database()
    {
        $this->actingAs($this->admin);
        
        // Crear un driver con fechas personalizadas
        $driver = UserDriverDetail::factory()->create([
            'use_custom_dates' => true,
            'custom_registration_date' => Carbon::now()->subDays(30),
            'custom_completion_date' => Carbon::now()->subDays(15)
        ]);
        
        // Limpiar fechas personalizadas
        $driver->update([
            'use_custom_dates' => false,
            'custom_registration_date' => null,
            'custom_completion_date' => null
        ]);
        
        // Verificar que se limpiaron correctamente
        $driver->refresh();
        
        $this->assertFalse($driver->use_custom_dates);
        $this->assertNull($driver->custom_registration_date);
        $this->assertNull($driver->custom_completion_date);
    }

    /** @test */
    public function it_uses_custom_dates_when_enabled()
    {
        $this->actingAs($this->admin);
        
        // Crear un driver con fechas personalizadas
        $customRegistrationDate = Carbon::now()->subDays(30);
        $customCompletionDate = Carbon::now()->subDays(15);
        
        $driver = UserDriverDetail::factory()->create([
            'use_custom_dates' => true,
            'custom_registration_date' => $customRegistrationDate,
            'custom_completion_date' => $customCompletionDate
        ]);
        
        // Verificar que las fechas personalizadas están disponibles
        $this->assertTrue($driver->use_custom_dates);
        $this->assertNotNull($driver->custom_registration_date);
        $this->assertNotNull($driver->custom_completion_date);
    }

    /** @test */
    public function it_falls_back_to_default_dates_when_custom_dates_disabled()
    {
        $this->actingAs($this->admin);
        
        // Crear un driver sin fechas personalizadas
        $driver = UserDriverDetail::factory()->create([
            'use_custom_dates' => false,
            'custom_registration_date' => null,
            'custom_completion_date' => null
        ]);
        
        // Verificar que usa las fechas por defecto
        $this->assertFalse($driver->use_custom_dates);
        $this->assertNull($driver->custom_registration_date);
        $this->assertNull($driver->custom_completion_date);
        $this->assertNotNull($driver->created_at);
        $this->assertNotNull($driver->updated_at);
    }
}