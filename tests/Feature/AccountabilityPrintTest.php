<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountabilityPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_uses_employee_record_and_active_admin_name(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $teacher->syncRoleProfile(['employee_number' => 'EMP-001']);
        $teacher->refresh();
        User::factory()->create(['role' => 'admin', 'name' => 'Active Verifier']);
        User::factory()->create(['role' => 'admin', 'is_active' => false, 'name' => 'Inactive Verifier']);

        $this->actingAs($teacher)->get('/teacher/my-properties/print')->assertOk()
            ->assertSee('EMP-001')->assertSee('Active Verifier')->assertDontSee('Inactive Verifier');
    }

    public function test_multiple_admins_require_explicit_selection_and_reject_non_admins(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        User::factory()->create(['role' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($teacher)->get('/teacher/my-properties/print')->assertOk()
            ->assertSee('Administrator not selected')->assertSee('Not yet provided');
        $this->get('/teacher/my-properties/print?verified_by='.$admin->id)->assertOk()
            ->assertViewHas('verifyingAdmin', fn ($verifier) => $verifier->is($admin));
        $this->get('/teacher/my-properties/print?verified_by='.$teacher->id)
            ->assertSessionHasErrors('verified_by');
    }

    public function test_print_form_has_signatures_without_app_navigation(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher)->get('/teacher/my-properties/print')
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee('Signature over Printed Name of Accountable End-User')
            ->assertSee('Signature over Printed Name of Administrator')
            ->assertSee('No assigned properties found.')
            ->assertDontSee('mobile-app-header')
            ->assertDontSee('sidebar-shell')
            ->assertDontSee('Print My Accountabilities | PARDS');
    }

    public function test_teacher_can_download_a_printable_pdf(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $response = $this->actingAs($teacher)->get('/teacher/my-properties/print?pdf=1');
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_print_and_pdf_require_teacher_access(): void
    {
        $this->get('/teacher/my-properties/print?pdf=1')->assertRedirect('/login');
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get('/teacher/my-properties/print')->assertForbidden();
        $this->get('/teacher/my-properties/print?pdf=1')->assertForbidden();
    }
}
