<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\OwnerOperatorDetail;
use App\Models\VehicleDriverAssignment;

use Illuminate\Foundation\Testing\RefreshDatabase;

class OwnerOperatorDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_vehicle_driver_assignment()
    {
        $assignment = VehicleDriverAssignment::factory()->create();
        $detail = OwnerOperatorDetail::factory()->create([
            'assignment_id' => $assignment->id
        ]);

        $this->assertInstanceOf(VehicleDriverAssignment::class, $detail->assignment);
        $this->assertEquals($assignment->id, $detail->assignment->id);
    }

    /** @test */
    public function it_has_required_fillable_attributes()
    {
        $fillable = [
            'assignment_id',
            'owner_name',
            'owner_phone',
            'owner_email',
            'contract_agreed',
            'notes'
        ];

        $ownerOperator = new OwnerOperatorDetail();
        
        $this->assertEquals($fillable, $ownerOperator->getFillable());
    }

    /** @test */
    public function it_can_be_created_with_all_attributes()
    {
        $data = [
            'owner_name' => 'John Smith',
            'owner_phone' => '555-0123',
            'owner_email' => 'john.smith@example.com',
            'contract_agreed' => true,
            'notes' => 'Reliable owner-operator with 10 years experience'
        ];
        
        $ownerOperator = OwnerOperatorDetail::factory()->create($data);

        $this->assertEquals($data['owner_name'], $ownerOperator->owner_name);
        $this->assertEquals($data['owner_phone'], $ownerOperator->owner_phone);
        $this->assertEquals($data['owner_email'], $ownerOperator->owner_email);
        $this->assertTrue($ownerOperator->contract_agreed);
        $this->assertEquals($data['notes'], $ownerOperator->notes);
    }

    /** @test */
    public function it_can_get_driver_name()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_name' => 'Jane Doe'
        ]);

        $this->assertEquals('Jane Doe', $ownerOperator->getDriverName());
    }

    /** @test */
    public function it_can_get_driver_phone()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_phone' => '555-9876'
        ]);

        $this->assertEquals('555-9876', $ownerOperator->getDriverPhone());
    }

    /** @test */
    public function it_can_get_driver_email()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_email' => 'owner@example.com'
        ]);

        $this->assertEquals('owner@example.com', $ownerOperator->getDriverEmail());
    }

    /** @test */
    public function it_validates_email_format()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_email' => 'valid@example.com'
        ]);

        $this->assertTrue(filter_var($ownerOperator->owner_email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /** @test */
    public function it_can_be_created_without_optional_fields()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_name' => 'Required Name',
            'owner_phone' => '555-0000',
            'owner_email' => null,
            'notes' => null
        ]);

        $this->assertEquals('Required Name', $ownerOperator->owner_name);
        $this->assertEquals('555-0000', $ownerOperator->owner_phone);
        $this->assertNull($ownerOperator->owner_email);
        $this->assertNull($ownerOperator->notes);
    }

    /** @test */
    public function it_can_scope_by_name()
    {
        OwnerOperatorDetail::factory()->create(['owner_name' => 'John Smith']);
        OwnerOperatorDetail::factory()->create(['owner_name' => 'Jane Doe']);
        OwnerOperatorDetail::factory()->create(['owner_name' => 'John Johnson']);

        $johnOwners = OwnerOperatorDetail::whereNameContains('John')->get();
        
        $this->assertCount(2, $johnOwners);
        $johnOwners->each(function ($owner) {
            $this->assertStringContainsString('John', $owner->owner_name);
        });
    }



    /** @test */
    public function it_can_format_contact_info()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_name' => 'John Smith',
            'owner_phone' => '555-0123',
            'owner_email' => 'john@example.com'
        ]);

        $contactInfo = $ownerOperator->getFormattedContactInfo();
        
        $this->assertStringContainsString('John Smith', $contactInfo);
        $this->assertStringContainsString('555-0123', $contactInfo);
        $this->assertStringContainsString('john@example.com', $contactInfo);
    }

    /** @test */
    public function it_handles_missing_contact_info_gracefully()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'owner_name' => 'John Smith',
            'owner_phone' => '555-0123',
            'owner_email' => null
        ]);

        $contactInfo = $ownerOperator->getFormattedContactInfo();
        
        $this->assertStringContainsString('John Smith', $contactInfo);
        $this->assertStringContainsString('555-0123', $contactInfo);
        $this->assertStringNotContainsString('null', $contactInfo);
    }



    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'contract_agreed' => '1'
        ]);

        $this->assertIsBool($ownerOperator->contract_agreed);
        $this->assertTrue($ownerOperator->contract_agreed);
    }

    /** @test */
    public function it_can_have_detailed_notes()
    {
        $notes = 'Owner-operator with excellent safety record. Prefers long-haul routes. Has own insurance coverage.';
        $ownerOperator = OwnerOperatorDetail::factory()->create([
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $ownerOperator->notes);
        $this->assertIsString($ownerOperator->notes);
    }
}