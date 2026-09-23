<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyRequestRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function setupRequest(): array
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $staff = User::factory()->create(['role' => 'staff']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::create([
            'property_code' => 'REQ-001', 'qr_reference' => 'REQ-001', 'qr_token' => 'REQ-001',
            'property_name' => 'Classroom Projector', 'quantity' => 3, 'unit' => 'units',
            'condition_status' => 'good', 'status' => 'available',
        ]);
        $record = PropertyRequestRecord::create([
            'requested_by' => $teacher->id, 'requested_item_name' => 'Classroom Projector',
            'requested_quantity' => 2, 'purpose' => 'Classroom lessons', 'status' => 'pending',
        ]);
        return [$teacher, $staff, $admin, $property, $record, '/property-requests/'.$record->id];
    }

    public function test_full_workflow_notifies_staff_then_assigns_once_and_protects_receipt(): void
    {
        [$teacher, $staff, $admin, $property, $record, $url] = $this->setupRequest();
        $this->actingAs($admin)->get('/property-requests')->assertDontSee('Classroom Projector');
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'awaiting_admin', 'response_notes' => 'Reviewed'])
            ->assertSessionHasNoErrors()->assertRedirect($url);
        $this->actingAs($admin)->get('/admin/dashboard')->assertSee('forwarded by staff need your approval');
        $this->get($url)->assertOk()->assertSee('Save Admin Decision');
        $this->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])
            ->assertSessionHasNoErrors()->assertRedirect($url);
        $this->assertDatabaseCount('assignments', 0);
        $this->assertSame(3, $property->fresh()->quantity);
        $this->actingAs($staff)->get('/staff/dashboard')->assertSee('admin-approved request(s)');
        $this->get($url)->assertOk()->assertSee('Confirm Assignment and Create Receipt');
        $units = collect(['PROJECTOR-A', 'PROJECTOR-B', 'PROJECTOR-C'])
            ->map(fn ($serial) => $property->units()->create(['serial_number' => $serial, 'status' => 'available']));
        $this->patch($url.'/status', ['status' => 'fulfilled', 'property_id' => $property->id, 'department' => 'ICT Department', 'select_units' => 1, 'unit_ids' => $units->take(2)->pluck('id')->all()])
            ->assertSessionHasNoErrors()->assertRedirect($url);
        $assignment = Assignment::firstOrFail();
        $this->assertSame($teacher->id, $assignment->teacher_id);
        $this->assertSame($staff->id, $assignment->assigned_by);
        $this->assertSame('ICT Department', $assignment->department);
        $this->assertSame(['PROJECTOR-A', 'PROJECTOR-B'], $assignment->propertyUnits()->orderBy('id')->pluck('serial_number')->all());
        $this->assertSame(2, $assignment->propertyUnits()->count());
        $this->assertSame(1, $property->fresh()->quantity);
        $this->assertSame($admin->id, $record->fresh()->processed_by);
        $this->assertSame('fulfilled', $record->fresh()->status);
        $this->get($url)->assertSee('Print Receiving Receipt');
        $this->patch($url.'/status', ['status' => 'fulfilled', 'property_id' => $property->id])->assertSessionHasErrors('status');
        $this->assertDatabaseCount('assignments', 1);
        $this->actingAs($teacher)->get($url.'/receipt')->assertOk()->assertSee('Property Receiving Copy')->assertSee($admin->name);
        $this->get('/teacher/property-requests')->assertSee('Print Receipt');
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($otherTeacher)->get($url.'/receipt')->assertForbidden();
    }

    public function test_roles_cannot_skip_staff_review_or_admin_approval(): void
    {
        [$teacher, $staff, $admin, $property, $record, $url] = $this->setupRequest();
        $this->actingAs($teacher)->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])->assertForbidden();
        $this->actingAs($admin)->get($url)->assertForbidden();
        $this->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])->assertSessionHasErrors('status');
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])->assertSessionHasErrors('status');
        $this->patch($url.'/status', ['status' => 'fulfilled', 'property_id' => $property->id])->assertSessionHasErrors('status');
        $this->get($url.'/receipt')->assertNotFound();
        $this->assertSame('pending', $record->fresh()->status);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_awaiting_stock_can_be_approved_later_and_stock_is_rechecked_at_assignment(): void
    {
        [$teacher, $staff, $admin, $property, $record, $url] = $this->setupRequest();
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'awaiting_admin'])->assertSessionHasNoErrors();
        $property->update(['quantity' => 0]);
        $this->actingAs($admin)->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])->assertSessionHasErrors('property_id');
        $this->patch($url.'/status', ['status' => 'awaiting_stock', 'response_notes' => 'Waiting for delivery'])->assertSessionHasNoErrors();
        $this->assertSame('awaiting_stock', $record->fresh()->status);
        $property->update(['quantity' => 3]);
        $this->patch($url.'/status', ['status' => 'approved', 'property_id' => $property->id])->assertSessionHasNoErrors();
        $property->update(['quantity' => 1]);
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'fulfilled', 'property_id' => $property->id])->assertSessionHasErrors('property_id');
        $this->assertSame('approved', $record->fresh()->status);
        $this->assertSame(1, $property->fresh()->quantity);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_rejection_requires_reason_and_cannot_be_assigned(): void
    {
        [$teacher, $staff, $admin, $property, $record, $url] = $this->setupRequest();
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'awaiting_admin'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch($url.'/status', ['status' => 'rejected'])->assertSessionHasErrors('response_notes');
        $this->patch($url.'/status', ['status' => 'rejected', 'response_notes' => 'Request is a duplicate'])->assertSessionHasNoErrors();
        $this->actingAs($staff)->patch($url.'/status', ['status' => 'fulfilled', 'property_id' => $property->id])->assertSessionHasErrors('status');
        $this->assertSame('rejected', $record->fresh()->status);
    }
}
