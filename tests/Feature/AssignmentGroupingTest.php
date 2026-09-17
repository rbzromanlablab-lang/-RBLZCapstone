<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyRequestRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentGroupingTest extends TestCase
{
    use RefreshDatabase;

    private function property(string $code, int $stock): Property
    {
        return Property::create([
            'property_code' => $code, 'qr_reference' => $code, 'qr_token' => $code,
            'property_name' => $code, 'quantity' => $stock, 'unit' => 'units',
            'condition_status' => 'good', 'status' => 'available', 'department' => 'Science Department',
        ]);
    }

    public function test_people_are_paginated_with_all_their_assignments_in_one_card(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $staff = User::factory()->create(['role' => 'staff']);
        $property = $this->property('GROUPED-PROPERTY', 0);
        foreach (range(1, 13) as $index) {
            Assignment::create([
                'property_id' => $property->id, 'teacher_id' => $index === 13 ? $staff->id : $teacher->id,
                'assigned_by' => $admin->id, 'quantity_assigned' => 1,
                'date_assigned' => today(), 'assigned_at' => today(), 'status' => 'active', 'department' => 'Science Department',
            ]);
        }
        $response = $this->actingAs($admin)->get('/assignments');
        $response->assertOk()->assertSee('Science Department');
        $response->assertViewHas('teacherAssignees', fn ($people) => $people->total() === 1 && $people->first()->teacherAssignments->count() === 12);
        $response->assertViewHas('staffAssignees', fn ($people) => $people->total() === 1 && $people->first()->id === $staff->id);
        $this->assertSame(1, substr_count($response->getContent(), 'data-assignee-id="'.$teacher->id.'"'));
        $this->get('/assignments?type=teachers')->assertSee($teacher->name)->assertDontSee($staff->email);
        $this->get('/assignments?type=staff')->assertSee($staff->name)->assertDontSee($teacher->email);
        $this->get('/assignments?search=Science')->assertOk()->assertSee($teacher->name)->assertSee($staff->name);
    }

    public function test_empty_stock_is_excluded_from_new_assignment_disposal_and_request_selectors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $staff = User::factory()->create(['role' => 'staff']);
        $empty = $this->property('EMPTY-STOCK', 0);
        $available = $this->property('AVAILABLE-STOCK', 3);
        $this->actingAs($staff)->get('/assignments/create')->assertOk()
            ->assertViewHas('properties', fn ($items) => !$items->contains('id', $empty->id) && $items->contains('id', $available->id));
        $this->get('/disposals/create')->assertOk()
            ->assertViewHas('properties', fn ($items) => !$items->contains('id', $empty->id));
        $record = PropertyRequestRecord::create([
            'requested_by' => $teacher->id, 'requested_item_name' => 'AVAILABLE-STOCK',
            'requested_quantity' => 2, 'purpose' => 'Teaching', 'status' => 'awaiting_admin',
            'reviewed_by' => $staff->id, 'reviewed_at' => now(),
        ]);
        $this->actingAs($admin)->get('/property-requests/'.$record->id)->assertOk()
            ->assertViewHas('properties', fn ($items) => !$items->contains('id', $empty->id) && $items->contains('id', $available->id));
    }

    public function test_property_department_is_saved_and_used_as_assignment_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $payload = [
            'property_code' => 'NEW-DEPT', 'property_name' => 'Department Laptop', 'quantity' => 1,
            'unit' => 'unit', 'condition_status' => 'good', 'status' => 'available', 'department' => 'ICT Department',
        ];
        $this->actingAs($admin)->post('/properties', $payload)->assertSessionHasNoErrors()->assertSessionHas('success');
        $property = Property::where('property_code', 'NEW-DEPT')->firstOrFail();
        $this->assertSame('ICT Department', $property->department);
        $payload['department'] = 'Science Department';
        $this->put('/properties/'.$property->id, $payload)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->get('/properties/'.$property->id)->assertSee('Science Department');
        $this->post('/assignments', [
            'property_id' => $property->id, 'teacher_id' => $teacher->id,
            'quantity_assigned' => 1, 'date_assigned' => today()->toDateString(),
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame('Science Department', Assignment::firstOrFail()->department);
    }
}
