<?php

namespace Tests\Feature;

use App\Models\PropertyRequestRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_submit_a_property_request(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.request@gmail.com',
        ]);

        $response = $this->actingAs($teacher)->post('/teacher/property-requests', [
            'requested_item_name' => 'Wireless Mouse',
            'requested_quantity' => 2,
            'needed_by' => '2026-05-01',
            'purpose' => 'Needed for classroom computer activities.',
            'additional_notes' => 'Prefer rechargeable units.',
        ]);

        $response->assertRedirect('/teacher/property-requests');

        $this->assertDatabaseHas('property_requests', [
            'requested_by' => $teacher->id,
            'requested_item_name' => 'Wireless Mouse',
            'requested_quantity' => 2,
            'status' => PropertyRequestRecord::STATUS_PENDING,
        ]);
    }

    public function test_teacher_dashboard_shows_property_request_entry_points(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.dashboard.request@gmail.com',
        ]);

        PropertyRequestRecord::query()->create([
            'requested_by' => $teacher->id,
            'requested_item_name' => 'Extension Cord',
            'requested_quantity' => 1,
            'needed_by' => '2026-05-03',
            'purpose' => 'Needed for classroom projector setup.',
            'status' => PropertyRequestRecord::STATUS_PENDING,
        ]);

        $response = $this->actingAs($teacher)->get('/teacher/dashboard');

        $response->assertOk();
        $response->assertSee('New Property Request');
        $response->assertSee('Request Tracking');
        $response->assertSee('Extension Cord');
    }

    public function test_supply_office_can_review_and_update_a_property_request(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff.review@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.review@gmail.com',
        ]);

        $propertyRequest = PropertyRequestRecord::query()->create([
            'requested_by' => $teacher->id,
            'requested_item_name' => 'Printer Ink',
            'requested_quantity' => 1,
            'needed_by' => '2026-05-05',
            'purpose' => 'Needed for printing instructional materials.',
            'status' => PropertyRequestRecord::STATUS_PENDING,
        ]);

        $showResponse = $this->actingAs($staff)->get('/property-requests/'.$propertyRequest->id);
        $showResponse->assertOk();
        $showResponse->assertSee('Confirm Request');
        $showResponse->assertSee('Printer Ink');

        $updateResponse = $this->actingAs($staff)->patch('/property-requests/'.$propertyRequest->id.'/status', [
            'status' => PropertyRequestRecord::STATUS_APPROVED,
            'response_notes' => 'Approved for next office release cycle.',
        ]);

        $updateResponse->assertRedirect('/property-requests/'.$propertyRequest->id);

        $this->assertDatabaseHas('property_requests', [
            'id' => $propertyRequest->id,
            'processed_by' => $staff->id,
            'status' => PropertyRequestRecord::STATUS_APPROVED,
            'response_notes' => 'Approved for next office release cycle.',
        ]);
    }
}
