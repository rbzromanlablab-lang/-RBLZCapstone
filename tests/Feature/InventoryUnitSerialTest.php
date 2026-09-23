<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryUnitSerialTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $serials = ['LAP-1', 'LAP-2', 'LAP-3', 'LAP-4', 'LAP-5']): array
    {
        return [
            'property_name' => 'Laptop', 'property_code' => 'ICT-001',
            'quantity' => count($serials), 'unit' => 'piece',
            'condition_status' => 'good', 'status' => 'available',
            'units' => array_map(fn ($serial) => ['id' => '', 'serial_number' => $serial], $serials),
        ];
    }

    private function createInventory(): Property
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/properties', $this->payload())->assertSessionHasNoErrors();
        return Property::where('property_code', 'ICT-001')->firstOrFail();
    }

    private function assignmentPayload(Property $property, array $ids): array
    {
        return [
            'property_id' => $property->id,
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'quantity_assigned' => count($ids), 'date_assigned' => '2026-09-23',
            'select_units' => 1, 'unit_ids' => $ids,
        ];
    }

    public function test_five_manual_serials_are_saved_on_five_units_and_shown_in_inventory(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $this->assertSame(['LAP-1', 'LAP-2', 'LAP-3', 'LAP-4', 'LAP-5'], $units->pluck('serial_number')->all());
        $this->assertCount(5, $units->pluck('qr_token')->unique());
        $this->get('/properties/'.$property->id)->assertOk()->assertSee('LAP-5');
        $this->get('/properties?search=LAP-5')->assertOk()->assertSee('ICT-001');
        $this->get('/properties/'.$property->id.'/edit')->assertOk()->assertSee('LAP-5');
        $this->get('/assignments/create')->assertOk()->assertSee('LAP-5')->assertDontSee('Assigned Item Serial Number(s)');
    }

    public function test_blank_serial_fields_generate_distinct_serials_at_creation(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/properties', $this->payload(['', '', '']))->assertSessionHasNoErrors();
        $serials = Property::firstOrFail()->units()->pluck('serial_number');
        $this->assertCount(3, $serials->unique());
        foreach ($serials as $serial) $this->assertStringStartsWith('SN-', $serial);
    }

    public function test_duplicate_serials_roll_back_the_entire_property_save(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson('/properties', $this->payload(['DUPLICATE', 'duplicate']))
            ->assertUnprocessable()->assertJsonValidationErrors('units.1.serial_number');
        $this->assertDatabaseCount('properties', 0);
        $this->assertDatabaseCount('property_units', 0);
        $this->assertDatabaseCount('property_histories', 0);
    }

    public function test_serial_count_and_serials_from_other_properties_are_rejected(): void
    {
        $this->createInventory();
        $payload = $this->payload(['LAP-5']);
        $payload['property_code'] = 'ICT-002';
        $this->post('/properties', $payload)->assertSessionHasErrors('units.0.serial_number');
        $payload['quantity'] = 2;
        $this->post('/properties', $payload)->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('properties', 1);
        $this->assertDatabaseCount('property_units', 5);
    }

    public function test_assignment_uses_exact_selected_units_and_rejects_stale_selection(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $data = $this->assignmentPayload($property, [$units[1]->id, $units[4]->id]);
        $this->post('/assignments', $data)->assertSessionHasNoErrors();
        $assignment = Assignment::firstOrFail();
        $this->assertSame('LAP-2, LAP-5', $assignment->unit_serial_numbers);
        $this->assertSame(3, $property->fresh()->quantity);
        $this->get('/assignments/'.$assignment->id.'/print')->assertOk()->assertSee('LAP-2, LAP-5')->assertDontSee('LAP-1');
        $this->post('/assignments', $data)->assertSessionHasErrors('unit_ids');
        $this->assertDatabaseCount('assignments', 1);
        $this->assertSame(3, $property->fresh()->quantity);
        $this->actingAs(User::findOrFail($data['teacher_id']))->get(route('teacher.properties.index'))
            ->assertOk()->assertSee('LAP-2, LAP-5')->assertDontSee('LAP-1');
    }

    public function test_assignment_rejects_foreign_units_and_wrong_selection_count(): void
    {
        $property = $this->createInventory();
        $other = $this->payload(['OTHER-1']);
        $other['property_code'] = 'OTHER';
        $this->post('/properties', $other)->assertSessionHasNoErrors();
        $foreignId = Property::where('property_code', 'OTHER')->firstOrFail()->units()->first()->id;
        $this->post('/assignments', $this->assignmentPayload($property, [$foreignId]))->assertSessionHasErrors('unit_ids');
        $data = $this->assignmentPayload($property, []);
        $data['quantity_assigned'] = 1;
        $this->post('/assignments', $data)->assertSessionHasErrors('unit_ids');
        $this->assertDatabaseCount('assignments', 0);
        $this->assertSame(5, $property->fresh()->quantity);
    }

    public function test_edit_preserves_unit_identity_and_cannot_rewrite_assigned_serials(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $this->post('/assignments', $this->assignmentPayload($property, [$units[0]->id]))->assertSessionHasNoErrors();
        $data = $this->payload(['LAP-2-EDITED', 'LAP-3', 'LAP-4', 'LAP-5']);
        foreach ($data['units'] as $index => &$row) $row['id'] = $units[$index + 1]->id;
        unset($row);
        $this->put('/properties/'.$property->id, $data)->assertSessionHasNoErrors();
        $this->assertSame($units[1]->qr_token, $units[1]->fresh()->qr_token);
        $this->assertSame('LAP-2-EDITED', $units[1]->fresh()->serial_number);
        $data['units'][0] = ['id' => $units[0]->id, 'serial_number' => 'REWRITTEN'];
        $this->put('/properties/'.$property->id, $data)->assertSessionHasErrors('units.0.serial_number');
        $this->assertSame('LAP-1', $units[0]->fresh()->serial_number);
        $this->assertSame('assigned', $units[0]->fresh()->status);
    }

    public function test_failed_assignment_edit_restores_original_units_and_stock(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $first = $this->assignmentPayload($property, [$units[0]->id]);
        $second = $this->assignmentPayload($property, [$units[1]->id]);
        $this->post('/assignments', $first)->assertSessionHasNoErrors();
        $assignment = Assignment::firstOrFail();
        $this->post('/assignments', $second)->assertSessionHasNoErrors();
        $first['unit_ids'] = [$units[1]->id];
        $this->put('/assignments/'.$assignment->id, $first)->assertSessionHasErrors('unit_ids');
        $this->assertSame($assignment->id, $units[0]->fresh()->assignment_id);
        $this->assertSame(3, $property->fresh()->quantity);
    }

    public function test_stock_changes_preserve_assigned_units_and_existing_qr_codes(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $this->post('/assignments', $this->assignmentPayload($property, [$units[0]->id]))->assertSessionHasNoErrors();
        $data = $this->payload(['LAP-2', 'NEW-STOCK']);
        $data['units'][0]['id'] = $units[1]->id;
        $this->put('/properties/'.$property->id, $data)->assertSessionHasNoErrors();
        $this->assertSame(2, $property->fresh()->quantity);
        $this->assertSame(3, $property->units()->count());
        $this->assertSame($units[1]->qr_token, $units[1]->fresh()->qr_token);
        $data['quantity'] = 0;
        $data['units'] = [];
        $this->put('/properties/'.$property->id, $data)->assertSessionHasNoErrors();
        $this->assertSame(0, $property->fresh()->quantity);
        $this->assertSame(1, $property->units()->count());
        $this->assertSame('assigned', $units[0]->fresh()->status);
        $this->get('/properties/'.$property->id.'/edit')->assertOk();
    }

    public function test_teacher_can_view_separate_assignments_of_the_same_property(): void
    {
        $property = $this->createInventory();
        $units = $property->units()->orderBy('id')->get();
        $data = $this->assignmentPayload($property, [$units[0]->id]);
        $this->post('/assignments', $data)->assertSessionHasNoErrors();
        $first = Assignment::firstOrFail();
        $data['unit_ids'] = [$units[1]->id];
        $this->post('/assignments', $data)->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail($data['teacher_id']))->get(route('teacher.properties.index'))
            ->assertOk()->assertSee('LAP-1')->assertSee('LAP-2')->assertDontSee('LAP-3');
        $this->get(route('teacher.properties.show', ['property' => $property->id, 'assignment' => $first->id]))
            ->assertOk()->assertSee('LAP-1')->assertDontSee('LAP-2');
        $this->get(route('teacher.properties.show', ['property' => $property->id, 'assignment' => 999999]))->assertNotFound();
    }
}
