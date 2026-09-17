<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentDepartmentSerialTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($staff);
        $property = Property::create([
            'property_code' => 'DEPT-001', 'qr_reference' => 'DEPT-001', 'qr_token' => 'DEPT-001',
            'property_name' => 'Laptop', 'quantity' => 4, 'unit' => 'units',
            'condition_status' => 'good', 'status' => 'available',
        ]);
        return [
            'property_id' => $property->id, 'teacher_id' => $teacher->id,
            'quantity_assigned' => 2, 'date_assigned' => '2026-09-17', 'department' => 'Science Department',
        ];
    }

    public function test_department_and_manual_serials_are_saved_and_printed_and_editable(): void
    {
        $data = $this->payload();
        $data['serial_numbers'] = "LAPTOP-A\nLAPTOP-B";
        $this->post('/assignments', $data)->assertSessionHasNoErrors();
        $assignment = Assignment::firstOrFail();
        $this->assertSame('Science Department', $assignment->department);
        $this->assertSame(['LAPTOP-A', 'LAPTOP-B'], $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->all());
        $this->get('/assignments/'.$assignment->id.'/print')->assertOk()->assertSee('Science Department')->assertSee('LAPTOP-A');
        $data['department'] = 'Math Department';
        $data['serial_numbers'] = "LAPTOP-A\nLAPTOP-C";
        $this->put('/assignments/'.$assignment->id, $data)->assertSessionHasNoErrors();
        $this->assertSame('Math Department', $assignment->fresh()->department);
        $this->assertSame(['LAPTOP-A', 'LAPTOP-C'], $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->all());
    }

    public function test_blank_serials_generate_unique_serials_and_retain_them_on_edit(): void
    {
        $data = $this->payload();
        $data['serial_numbers'] = '';
        $this->post('/assignments', $data)->assertSessionHasNoErrors();
        $assignment = Assignment::firstOrFail();
        $serials = $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->all();
        $this->assertCount(2, array_unique($serials));
        foreach ($serials as $serial) $this->assertStringStartsWith('SN-', $serial);
        $this->put('/assignments/'.$assignment->id, $data)->assertSessionHasNoErrors();
        $this->assertSame($serials, $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->all());
    }

    public function test_duplicate_or_excess_serials_roll_back_assignment_and_inventory(): void
    {
        $data = $this->payload();
        $this->post('/assignments', $data + ['serial_numbers' => "DUPLICATE\nDUPLICATE"])->assertSessionHasErrors('serial_numbers');
        $this->post('/assignments', $data + ['serial_numbers' => "ONE\nTWO\nTHREE"])->assertSessionHasErrors('serial_numbers');
        $this->assertDatabaseCount('assignments', 0);
        $this->assertSame(4, Property::findOrFail($data['property_id'])->quantity);
    }
}
