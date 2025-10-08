<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\CompanyDriverDetail;
use App\Models\VehicleDriverAssignment;
use App\Models\DriverApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyDriverDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_vehicle_driver_assignment()
    {
        $assignment = VehicleDriverAssignment::factory()->create();
        $detail = CompanyDriverDetail::factory()->create([
            'assignment_id' => $assignment->id
        ]);

        $this->assertInstanceOf(VehicleDriverAssignment::class, $detail->assignment);
        $this->assertEquals($assignment->id, $detail->assignment->id);
    }

    /** @test */
    public function it_has_employee_information()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'employee_id' => 'EMP001',
            'department' => 'Transportation'
        ]);

        $this->assertEquals('EMP001', $companyDriver->employee_id);
        $this->assertEquals('Transportation', $companyDriver->department);
    }

    /** @test */
    public function it_has_supervisor_information()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'supervisor_name' => 'John Supervisor',
            'supervisor_phone' => '555-0123'
        ]);

        $this->assertEquals('John Supervisor', $companyDriver->supervisor_name);
        $this->assertEquals('555-0123', $companyDriver->supervisor_phone);
    }

    /** @test */
    public function it_has_salary_information()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'salary_type' => 'hourly',
            'base_rate' => 25.50,
            'overtime_rate' => 38.25
        ]);

        $this->assertEquals('hourly', $companyDriver->salary_type);
        $this->assertEquals(25.50, $companyDriver->base_rate);
        $this->assertEquals(38.25, $companyDriver->overtime_rate);
    }

    /** @test */
    public function it_has_benefits_eligibility()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'benefits_eligible' => true
        ]);

        $this->assertTrue($companyDriver->benefits_eligible);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'assignment_id',
            'driver_application_id',
            'employee_id',
            'department',
            'supervisor_name',
            'supervisor_phone',
            'salary_type',
            'base_rate',
            'overtime_rate',
            'benefits_eligible',
            'notes'
        ];

        $companyDriver = new CompanyDriverDetail();
        
        $this->assertEquals($fillable, $companyDriver->getFillable());
    }

    /** @test */
    public function it_can_be_created_with_all_fields()
    {
        $data = [
            'employee_id' => 'EMP001',
            'department' => 'Transportation',
            'supervisor_name' => 'John Supervisor',
            'supervisor_phone' => '555-0123',
            'salary_type' => 'hourly',
            'base_rate' => 25.50,
            'overtime_rate' => 38.25,
            'benefits_eligible' => true
        ];
        
        $companyDriver = CompanyDriverDetail::factory()->create($data);

        $this->assertEquals('EMP001', $companyDriver->employee_id);
        $this->assertEquals('Transportation', $companyDriver->department);
        $this->assertEquals('John Supervisor', $companyDriver->supervisor_name);
        $this->assertEquals('555-0123', $companyDriver->supervisor_phone);
        $this->assertEquals('hourly', $companyDriver->salary_type);
        $this->assertEquals(25.50, $companyDriver->base_rate);
        $this->assertEquals(38.25, $companyDriver->overtime_rate);
        $this->assertTrue($companyDriver->benefits_eligible);
    }

    /** @test */
    public function it_can_be_created_with_minimal_fields()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'employee_id' => 'EMP002',
            'department' => 'Logistics'
        ]);

        $this->assertEquals('EMP002', $companyDriver->employee_id);
        $this->assertEquals('Logistics', $companyDriver->department);
    }



    /** @test */
    public function it_can_have_notes()
    {
        $notes = 'Special instructions for this company driver';
        $companyDriver = CompanyDriverDetail::factory()->create([
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $companyDriver->notes);
    }

    /** @test */
    public function it_casts_decimal_fields_correctly()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'base_rate' => '25.50',
            'overtime_rate' => '38.25'
        ]);

        $this->assertIsFloat($companyDriver->base_rate);
        $this->assertIsFloat($companyDriver->overtime_rate);
        $this->assertEquals(25.50, $companyDriver->base_rate);
        $this->assertEquals(38.25, $companyDriver->overtime_rate);
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $companyDriver = CompanyDriverDetail::factory()->create([
            'benefits_eligible' => '1'
        ]);

        $this->assertIsBool($companyDriver->benefits_eligible);
        $this->assertTrue($companyDriver->benefits_eligible);
    }
}