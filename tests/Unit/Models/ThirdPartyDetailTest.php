<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ThirdPartyDetail;
use App\Models\VehicleDriverAssignment;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ThirdPartyDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_vehicle_driver_assignment()
    {
        $assignment = VehicleDriverAssignment::factory()->create();
        $detail = ThirdPartyDetail::factory()->create([
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
            'third_party_name',
            'third_party_phone',
            'third_party_email',
            'third_party_dba',
            'third_party_address',
            'third_party_contact',
            'third_party_fein',
            'email_sent',
            'notes'
        ];

        $thirdParty = new ThirdPartyDetail();
        
        $this->assertEquals($fillable, $thirdParty->getFillable());
    }

    /** @test */
    public function it_can_be_created_with_all_attributes()
    {
        $data = [
            'third_party_name' => 'Mike Johnson',
            'third_party_phone' => '555-0123',
            'third_party_email' => 'mike@example.com',
            'third_party_dba' => 'ABC Transport LLC',
            'third_party_address' => '123 Main St, City, State 12345',
            'third_party_contact' => 'John Doe',
            'third_party_fein' => '12-3456789',
            'notes' => 'Contracted driver from reliable partner company'
        ];
        
        $thirdParty = ThirdPartyDetail::factory()->create($data);

        $this->assertEquals($data['third_party_name'], $thirdParty->third_party_name);
        $this->assertEquals($data['third_party_phone'], $thirdParty->third_party_phone);
        $this->assertEquals($data['third_party_email'], $thirdParty->third_party_email);
        $this->assertEquals($data['third_party_dba'], $thirdParty->third_party_dba);
        $this->assertEquals($data['third_party_address'], $thirdParty->third_party_address);
        $this->assertEquals($data['third_party_contact'], $thirdParty->third_party_contact);
        $this->assertEquals($data['third_party_fein'], $thirdParty->third_party_fein);
        $this->assertEquals($data['notes'], $thirdParty->notes);
    }

    /** @test */
    public function it_can_be_created_with_minimal_attributes()
    {
        $data = [
            'third_party_name' => 'Sarah Wilson',
            'third_party_phone' => '555-7777'
        ];
        
        $thirdParty = ThirdPartyDetail::factory()->create($data);

        $this->assertEquals($data['third_party_name'], $thirdParty->third_party_name);
        $this->assertEquals($data['third_party_phone'], $thirdParty->third_party_phone);
    }

    /** @test */
    public function it_validates_email_formats()
    {
        $thirdParty = ThirdPartyDetail::factory()->create([
            'third_party_email' => 'valid.email@example.com'
        ]);

        $this->assertTrue(filter_var($thirdParty->third_party_email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /** @test */
    public function it_can_be_created_without_optional_fields()
    {
        $thirdParty = ThirdPartyDetail::factory()->create([
            'third_party_name' => 'Required Name',
            'third_party_phone' => '555-0000',
            'third_party_email' => null,
            'third_party_dba' => null,
            'third_party_address' => null,
            'third_party_contact' => null,
            'third_party_fein' => null,
            'notes' => null
        ]);

        $this->assertEquals('Required Name', $thirdParty->third_party_name);
        $this->assertEquals('555-0000', $thirdParty->third_party_phone);
        $this->assertNull($thirdParty->third_party_email);
        $this->assertNull($thirdParty->third_party_dba);
        $this->assertNull($thirdParty->third_party_address);
        $this->assertNull($thirdParty->third_party_contact);
        $this->assertNull($thirdParty->third_party_fein);
        $this->assertNull($thirdParty->notes);
    }









    /** @test */
    public function it_can_have_detailed_notes()
    {
        $notes = 'Third-party contractor from ABC Logistics. Requires special insurance verification. Contact person: John Smith.';
        $thirdParty = ThirdPartyDetail::factory()->create([
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $thirdParty->notes);
        $this->assertIsString($thirdParty->notes);
    }

    /** @test */
    public function it_validates_fein_format()
    {
        $thirdParty = ThirdPartyDetail::factory()->create([
            'third_party_fein' => '12-3456789'
        ]);

        // Basic FEIN format validation (XX-XXXXXXX)
        $this->assertMatchesRegularExpression('/^\d{2}-\d{7}$/', $thirdParty->third_party_fein);
    }
}