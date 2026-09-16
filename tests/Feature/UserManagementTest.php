<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Disposal;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_user_with_active_assignments_and_restore_inventory(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.delete.user@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.delete.user@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Tablet',
            'property_code' => 'PAR-DELETE-001',
            'category' => 'ICT',
            'description' => 'Property assigned to a deletable user',
            'quantity' => 0,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Room 101',
            'qr_reference' => 'qr-delete-001',
            'qr_token' => 'qr-delete-001',
        ]);

        Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'teacher_profile_id' => $teacher->teacherProfile?->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 2,
            'date_assigned' => '2026-04-20',
            'assigned_at' => '2026-04-20',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'Room 101',
        ]);

        $response = $this->actingAs($admin)->delete('/users/'.$teacher->id);

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id,
        ]);
        $this->assertDatabaseMissing('assignments', [
            'teacher_id' => $teacher->id,
        ]);
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'quantity' => 2,
            'status' => Property::STATUS_AVAILABLE,
        ]);
    }

    public function test_admin_can_delete_a_user_without_removing_existing_disposal_records(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.dispose.delete@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Broken Chair',
            'property_code' => 'PAR-DELETE-002',
            'category' => 'Furniture',
            'description' => 'Disposed property record',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_POOR,
            'status' => Property::STATUS_DISPOSED,
            'location' => 'Storage',
            'qr_reference' => 'qr-delete-002',
            'qr_token' => 'qr-delete-002',
        ]);

        $disposal = Disposal::query()->create([
            'property_id' => $property->id,
            'disposed_by' => $admin->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-04-20',
            'disposal_reason' => 'Beyond repair',
            'disposal_method' => 'destruction',
            'remarks' => 'Old broken chair',
            'status' => Disposal::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'second.admin@gmail.com',
        ]))->delete('/users/'.$admin->id);

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $admin->id,
        ]);
        $this->assertDatabaseHas('disposals', [
            'id' => $disposal->id,
            'disposed_by' => null,
        ]);
    }
}
