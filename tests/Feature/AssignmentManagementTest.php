<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Disposal;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_property_to_staff(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.account@gmail.com',
        ]);

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff.account@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Laptop',
            'property_code' => 'PAR-TEST-001',
            'category' => 'ICT',
            'description' => 'Assignment test property',
            'quantity' => 5,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_AVAILABLE,
            'location' => 'Office',
            'qr_reference' => 'qr-par-test-001',
            'qr_token' => 'qr-par-test-001',
        ]);

        $response = $this->actingAs($admin)->post('/assignments', [
            'property_id' => $property->id,
            'staff_id' => $staff->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'remarks' => 'Assigned to staff member',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', [
            'property_id' => $property->id,
            'teacher_id' => $staff->id,
        ]);
    }

    public function test_assignment_index_separates_teacher_and_staff_records(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.account@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Teacher Person',
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.person@gmail.com',
        ]);

        $staff = User::factory()->create([
            'name' => 'Staff Person',
            'role' => User::ROLE_STAFF,
            'email' => 'staff.person@gmail.com',
        ]);

        $propertyOne = Property::query()->create([
            'property_name' => 'Projector',
            'property_code' => 'PAR-TEST-002',
            'category' => 'AV',
            'description' => 'Teacher assignment property',
            'quantity' => 2,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Room 1',
            'qr_reference' => 'qr-par-test-002',
            'qr_token' => 'qr-par-test-002',
        ]);

        $propertyTwo = Property::query()->create([
            'property_name' => 'Printer',
            'property_code' => 'PAR-TEST-003',
            'category' => 'Office',
            'description' => 'Staff assignment property',
            'quantity' => 1,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Office',
            'qr_reference' => 'qr-par-test-003',
            'qr_token' => 'qr-par-test-003',
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyOne->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyTwo->id,
            'teacher_id' => $staff->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->get('/assignments');

        $response->assertOk();
        $response->assertSee('Teacher Assignments');
        $response->assertSee('Staff Assignments');
        $response->assertSee('Teacher Person');
        $response->assertSee('Staff Person');
    }

    public function test_assignment_generates_serial_numbers_per_assigned_piece_and_reduces_available_quantity(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.unit.assignment@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.unit.assignment@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Laboratory Chair',
            'property_code' => 'PAR-UNIT-001',
            'category' => 'Furniture',
            'description' => 'Per-piece serial assignment test',
            'quantity' => 5,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_AVAILABLE,
            'location' => 'Laboratory',
            'qr_reference' => 'qr-par-unit-001',
            'qr_token' => 'qr-par-unit-001',
        ]);

        $response = $this->actingAs($admin)->post('/assignments', [
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'quantity_assigned' => 2,
            'date_assigned' => '2026-04-14',
            'remarks' => 'Assigned two pieces to teacher',
        ]);

        $response->assertRedirect();

        $assignment = Assignment::query()->where('property_id', $property->id)->firstOrFail();
        $property->refresh();

        $this->assertSame(3, $property->quantity);
        $this->assertSame(2, $assignment->propertyUnits()->count());
        $this->assertSame(2, PropertyUnit::query()
            ->where('property_id', $property->id)
            ->where('assignment_id', $assignment->id)
            ->where('status', PropertyUnit::STATUS_ASSIGNED)
            ->count());

        $detailResponse = $this->actingAs($admin)->get('/assignments/'.$assignment->id);

        $detailResponse->assertOk();
        $detailResponse->assertSee('Assigned Serial Numbers');
        $assignment->propertyUnits->each(function (PropertyUnit $unit) use ($detailResponse): void {
            $detailResponse->assertSee($unit->serial_number);
        });
    }

    public function test_assignment_index_can_filter_by_assignee_type(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.filter@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Teacher Filtered',
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.filtered@gmail.com',
        ]);

        $staff = User::factory()->create([
            'name' => 'Staff Filtered',
            'role' => User::ROLE_STAFF,
            'email' => 'staff.filtered@gmail.com',
        ]);

        $propertyOne = Property::query()->create([
            'property_name' => 'Monitor',
            'property_code' => 'PAR-TEST-004',
            'category' => 'ICT',
            'description' => 'Teacher filter property',
            'quantity' => 1,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Lab',
            'qr_reference' => 'qr-par-test-004',
            'qr_token' => 'qr-par-test-004',
        ]);

        $propertyTwo = Property::query()->create([
            'property_name' => 'Scanner',
            'property_code' => 'PAR-TEST-005',
            'category' => 'Office',
            'description' => 'Staff filter property',
            'quantity' => 1,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Office',
            'qr_reference' => 'qr-par-test-005',
            'qr_token' => 'qr-par-test-005',
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyOne->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyTwo->id,
            'teacher_id' => $staff->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        $teachersOnly = $this->actingAs($admin)->get('/assignments?type=teachers');
        $teachersOnly->assertOk();
        $teachersOnly->assertSee('Teacher Assignments');
        $teachersOnly->assertDontSee('Staff Assignments');

        $staffOnly = $this->actingAs($admin)->get('/assignments?type=staff');
        $staffOnly->assertOk();
        $staffOnly->assertSee('Staff Assignments');
        $staffOnly->assertDontSee('Teacher Assignments');
    }

    public function test_assignment_index_can_search_by_property_or_assignee(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.search@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Maria Teacher',
            'role' => User::ROLE_TEACHER,
            'email' => 'maria.teacher@gmail.com',
        ]);

        $staff = User::factory()->create([
            'name' => 'Carlo Staff',
            'role' => User::ROLE_STAFF,
            'email' => 'carlo.staff@gmail.com',
        ]);

        $propertyOne = Property::query()->create([
            'property_name' => 'Science Microscope',
            'property_code' => 'PAR-TEST-006',
            'category' => 'Lab',
            'description' => 'Teacher search property',
            'quantity' => 1,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Science Room',
            'qr_reference' => 'qr-par-test-006',
            'qr_token' => 'qr-par-test-006',
        ]);

        $propertyTwo = Property::query()->create([
            'property_name' => 'Office Stapler',
            'property_code' => 'PAR-TEST-007',
            'category' => 'Office',
            'description' => 'Staff search property',
            'quantity' => 1,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Office',
            'qr_reference' => 'qr-par-test-007',
            'qr_token' => 'qr-par-test-007',
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyOne->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        \App\Models\Assignment::query()->create([
            'property_id' => $propertyTwo->id,
            'teacher_id' => $staff->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => \App\Models\Assignment::STATUS_ACTIVE,
        ]);

        $searchByProperty = $this->actingAs($admin)->get('/assignments?search=Microscope');
        $searchByProperty->assertOk();
        $searchByProperty->assertSee('Science Microscope');
        $searchByProperty->assertDontSee('Office Stapler');

        $searchByAssignee = $this->actingAs($admin)->get('/assignments?search=Carlo');
        $searchByAssignee->assertOk();
        $searchByAssignee->assertSee('Carlo Staff');
        $searchByAssignee->assertDontSee('Maria Teacher');
    }

    public function test_admin_can_update_active_assignment_and_adjust_inventory(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.update@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.update@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Desktop Computer',
            'property_code' => 'PAR-TEST-008',
            'category' => 'ICT',
            'description' => 'Editable assignment property',
            'quantity' => 3,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Computer Lab',
            'qr_reference' => 'qr-par-test-008',
            'qr_token' => 'qr-par-test-008',
        ]);

        $assignment = Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'Computer Lab',
        ]);

        $response = $this->actingAs($admin)->put('/assignments/'.$assignment->id, [
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'quantity_assigned' => 2,
            'date_assigned' => '2026-04-20',
            'location' => 'ICT Room',
            'remarks' => 'Updated assignment quantity',
        ]);

        $response->assertRedirect('/assignments/'.$assignment->id);

        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'quantity_assigned' => 2,
            'location' => 'ICT Room',
            'remarks' => 'Updated assignment quantity',
        ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'quantity' => 2,
            'status' => Property::STATUS_ASSIGNED,
        ]);
    }

    public function test_admin_can_record_disposal_from_assigned_item(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.disposal@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.disposal@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'LCD Projector',
            'property_code' => 'PAR-TEST-009',
            'category' => 'AV',
            'description' => 'Damaged assigned property',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_DAMAGED,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Room 12',
            'qr_reference' => 'qr-par-test-009',
            'qr_token' => 'qr-par-test-009',
        ]);

        $assignment = Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 2,
            'date_assigned' => '2026-04-14',
            'assigned_at' => '2026-04-14',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'Room 12',
        ]);

        $response = $this->actingAs($admin)->post('/disposals', [
            'assignment_id' => $assignment->id,
            'property_id' => $property->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-04-20',
            'disposal_reason' => 'Damaged while assigned',
            'disposal_method' => 'destruction',
            'remarks' => 'Bulb and board are no longer working',
        ]);

        $disposal = Disposal::query()->first();

        $response->assertRedirect('/disposals/'.$disposal?->id);

        $this->assertDatabaseHas('disposals', [
            'property_id' => $property->id,
            'quantity_disposed' => 1,
            'disposal_reason' => 'Damaged while assigned',
        ]);

        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'quantity_assigned' => 1,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'quantity' => 0,
            'status' => Property::STATUS_ASSIGNED,
        ]);
    }

    public function test_staff_can_submit_disposal_request_for_admin_review(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff.disposal.request@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Office Fan',
            'property_code' => 'PAR-TEST-011',
            'category' => 'Office',
            'description' => 'Property for disposal request review',
            'quantity' => 2,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_POOR,
            'status' => Property::STATUS_AVAILABLE,
            'location' => 'Faculty Room',
            'qr_reference' => 'qr-par-test-011',
            'qr_token' => 'qr-par-test-011',
        ]);

        $response = $this->actingAs($staff)->post('/disposals', [
            'property_id' => $property->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-04-21',
            'disposal_reason' => 'Defective motor',
            'disposal_method' => 'destruction',
            'remarks' => 'Requesting admin approval for disposal.',
        ]);

        $disposal = Disposal::query()->first();

        $response->assertRedirect('/disposals/'.$disposal?->id);

        $this->assertDatabaseHas('disposals', [
            'id' => $disposal?->id,
            'property_id' => $property->id,
            'disposed_by' => $staff->id,
            'status' => Disposal::STATUS_PENDING,
            'processed_by' => null,
        ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'quantity' => 2,
            'status' => Property::STATUS_AVAILABLE,
        ]);
    }

    public function test_admin_can_approve_pending_disposal_request(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.disposal.approval@gmail.com',
        ]);

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff.disposal.approval@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Portable Speaker',
            'property_code' => 'PAR-TEST-012',
            'category' => 'AV',
            'description' => 'Pending disposal request property',
            'quantity' => 2,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_POOR,
            'status' => Property::STATUS_AVAILABLE,
            'location' => 'AV Room',
            'qr_reference' => 'qr-par-test-012',
            'qr_token' => 'qr-par-test-012',
        ]);

        $disposal = Disposal::query()->create([
            'property_id' => $property->id,
            'disposed_by' => $staff->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-04-21',
            'disposal_reason' => 'Broken audio output',
            'disposal_method' => 'destruction',
            'remarks' => 'Needs admin approval.',
            'status' => Disposal::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->patch('/disposals/'.$disposal->id.'/status', [
            'status' => Disposal::STATUS_APPROVED,
            'response_notes' => 'Approved for disposal processing.',
        ]);

        $response->assertRedirect('/disposals/'.$disposal->id);

        $this->assertDatabaseHas('disposals', [
            'id' => $disposal->id,
            'status' => Disposal::STATUS_APPROVED,
            'processed_by' => $admin->id,
            'response_notes' => 'Approved for disposal processing.',
        ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'quantity' => 1,
            'status' => Property::STATUS_AVAILABLE,
        ]);
    }

    public function test_user_index_shows_add_property_action_for_teacher_and_staff(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.users@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.users@gmail.com',
        ]);

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'email' => 'staff.users@gmail.com',
        ]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $response->assertSee(route('assignments.create', ['teacher_id' => $teacher->id]), false);
        $response->assertSee(route('assignments.create', ['staff_id' => $staff->id]), false);
        $response->assertSee('Add Property');
    }

    public function test_assignment_create_can_prefill_teacher_and_staff_from_user_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.prefill@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Teacher Prefill',
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.prefill@gmail.com',
        ]);

        $staff = User::factory()->create([
            'name' => 'Staff Prefill',
            'role' => User::ROLE_STAFF,
            'email' => 'staff.prefill@gmail.com',
        ]);

        $teacherResponse = $this->actingAs($admin)->get('/assignments/create?teacher_id='.$teacher->id);
        $teacherResponse->assertOk();
        $teacherResponse->assertSee('Assigning To '.$teacher->name);
        $teacherResponse->assertSee('option value="'.$teacher->id.'" selected', false);

        $staffResponse = $this->actingAs($admin)->get('/assignments/create?staff_id='.$staff->id);
        $staffResponse->assertOk();
        $staffResponse->assertSee('Assigning To '.$staff->name);
        $staffResponse->assertSee('option value="'.$staff->id.'" selected', false);
    }

    public function test_assignment_pages_show_property_code_for_assigned_items(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.property.numbers@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.property.numbers@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Office Tablet',
            'property_code' => 'PAR-TEST-010',
            'category' => 'ICT',
            'description' => 'Property used in assignment details',
            'quantity' => 3,
            'unit' => 'units',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'location' => 'Supply Office',
            'qr_reference' => 'qr-par-test-010',
            'qr_token' => 'qr-par-test-010',
        ]);

        $assignment = Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 2,
            'date_assigned' => '2026-04-21',
            'assigned_at' => '2026-04-21',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'Supply Office',
        ]);

        $detailResponse = $this->actingAs($admin)->get('/assignments/'.$assignment->id);
        $detailResponse->assertOk();
        $detailResponse->assertSee('Property Code');
        $detailResponse->assertSee('PAR-TEST-010');
        $detailResponse->assertDontSee('Assigned Property Numbers');

        $indexResponse = $this->actingAs($admin)->get('/assignments');
        $indexResponse->assertOk();
        $indexResponse->assertSee('PAR-TEST-010');
        $indexResponse->assertDontSee('Assigned Property Numbers');
    }

    public function test_admin_can_print_assignment_receiving_copy(): void
    {
        $admin = User::factory()->create([
            'name' => 'Supply Admin',
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.assignment.print@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Teacher Receiver',
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.assignment.print@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Office Laptop',
            'property_code' => 'PAR-PRINT-001',
            'serial_number' => 'SN-PRINT-001',
            'category' => 'ICT',
            'description' => 'Printable assignment receipt property',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'office' => 'Registrar Office',
            'location' => 'Registrar Room',
            'qr_reference' => 'qr-par-print-001',
            'qr_token' => 'qr-par-print-001',
        ]);

        $assignment = Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-21',
            'assigned_at' => '2026-04-21',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'Registrar Room',
        ]);

        $property->units()->create([
            'assignment_id' => $assignment->id,
            'serial_number' => 'UNIT-PRINT-001',
            'status' => \App\Models\PropertyUnit::STATUS_ASSIGNED,
        ]);

        $response = $this->actingAs($admin)->get('/assignments/'.$assignment->id.'/print');

        $response->assertOk();
        $response->assertSee('Property Receiving Copy');
        $response->assertSee('Teacher Receiver');
        $response->assertSee('Supply Admin');
        $response->assertSee('UNIT-PRINT-001');
        $response->assertSee('Admin / Supply Officer Signature');
        $response->assertSee('Receiver / End-User Signature');
    }

    public function test_return_logs_show_and_filter_who_received_and_who_recorded_return(): void
    {
        $admin = User::factory()->create([
            'name' => 'Return Admin',
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.return.logs@gmail.com',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Return Teacher',
            'role' => User::ROLE_TEACHER,
            'email' => 'teacher.return.logs@gmail.com',
        ]);

        $otherTeacher = User::factory()->create([
            'name' => 'Other Teacher',
            'role' => User::ROLE_TEACHER,
            'email' => 'other.return.logs@gmail.com',
        ]);

        $property = Property::query()->create([
            'property_name' => 'Return Laptop',
            'property_code' => 'PAR-RETURN-001',
            'serial_number' => 'SN-RETURN-001',
            'category' => 'ICT',
            'description' => 'Return log test property',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_GOOD,
            'status' => Property::STATUS_ASSIGNED,
            'office' => 'ICT Office',
            'location' => 'ICT Room',
            'qr_reference' => 'qr-par-return-001',
            'qr_token' => 'qr-par-return-001',
        ]);

        $assignment = Assignment::query()->create([
            'property_id' => $property->id,
            'teacher_id' => $teacher->id,
            'assigned_by' => $admin->id,
            'quantity_assigned' => 1,
            'date_assigned' => '2026-04-21',
            'assigned_at' => '2026-04-21',
            'status' => Assignment::STATUS_ACTIVE,
            'location' => 'ICT Room',
        ]);

        $property->units()->create([
            'assignment_id' => $assignment->id,
            'serial_number' => 'UNIT-RETURN-001',
            'status' => PropertyUnit::STATUS_ASSIGNED,
        ]);

        $returnResponse = $this->actingAs($admin)->patch('/assignments/'.$assignment->id.'/return', [
            'returned_at' => '2026-05-01',
        ]);

        $returnResponse->assertRedirect();

        $this->assertDatabaseHas('returns', [
            'assignment_id' => $assignment->id,
            'returned_by' => $admin->id,
            'return_date' => '2026-05-01 00:00:00',
        ]);

        $indexResponse = $this->actingAs($admin)->get('/returns');

        $indexResponse->assertOk();
        $indexResponse->assertSee('Return Laptop');
        $indexResponse->assertSee('Return Teacher');
        $indexResponse->assertSee('Return Admin');
        $indexResponse->assertSee('UNIT-RETURN-001');

        $filteredResponse = $this->actingAs($admin)->get('/returns?assignee_id='.$otherTeacher->id);

        $filteredResponse->assertOk();
        $filteredResponse->assertDontSee('Return Laptop');
    }

    public function test_admin_can_filter_disposal_records_by_method_status_and_search(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email' => 'admin.disposal.filter@gmail.com',
        ]);

        $staff = User::factory()->create([
            'name' => 'Supply Staff',
            'role' => User::ROLE_STAFF,
            'email' => 'staff.disposal.filter@gmail.com',
        ]);

        $donationProperty = Property::query()->create([
            'property_name' => 'Donation Printer',
            'property_code' => 'PAR-DONATE-001',
            'serial_number' => 'SN-DONATE-001',
            'category' => 'Office',
            'description' => 'Property for donation filtering',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_FAIR,
            'status' => Property::STATUS_DISPOSED,
            'office' => 'Registrar Office',
            'location' => 'Registrar Room',
            'qr_reference' => 'qr-par-donate-001',
            'qr_token' => 'qr-par-donate-001',
        ]);

        $destroyedProperty = Property::query()->create([
            'property_name' => 'Broken Monitor',
            'property_code' => 'PAR-DESTROY-001',
            'serial_number' => 'SN-DESTROY-001',
            'category' => 'ICT',
            'description' => 'Property for destruction filtering',
            'quantity' => 0,
            'unit' => 'unit',
            'date_acquired' => '2026-01-01',
            'condition_status' => Property::CONDITION_DAMAGED,
            'status' => Property::STATUS_FOR_DISPOSAL,
            'office' => 'ICT Office',
            'location' => 'ICT Room',
            'qr_reference' => 'qr-par-destroy-001',
            'qr_token' => 'qr-par-destroy-001',
        ]);

        Disposal::query()->create([
            'property_id' => $donationProperty->id,
            'disposed_by' => $staff->id,
            'processed_by' => $admin->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-05-01',
            'disposal_reason' => 'For donation to another office',
            'disposal_method' => 'donation',
            'remarks' => 'Donation request',
            'processed_at' => now(),
            'status' => Disposal::STATUS_COMPLETED,
        ]);

        Disposal::query()->create([
            'property_id' => $destroyedProperty->id,
            'disposed_by' => $staff->id,
            'quantity_disposed' => 1,
            'disposal_date' => '2026-05-03',
            'disposal_reason' => 'Damaged beyond repair',
            'disposal_method' => 'destruction',
            'remarks' => 'For disposal review',
            'status' => Disposal::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->get('/disposals?method=donation&status=completed&search=Registrar');

        $response->assertOk();
        $response->assertSee('Donation Printer');
        $response->assertSee('Registrar Office');
        $response->assertSee('Donation');
        $response->assertSee('Completed');
        $response->assertDontSee('Broken Monitor');
    }
}
