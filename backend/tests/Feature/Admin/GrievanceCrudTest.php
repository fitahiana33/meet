<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Role;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrievanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_grievance_with_audit_history(): void
    {
        $admin = $this->makeAdmin();
        $sector = Sector::create(['name' => 'Voirie']);
        $department = Department::create(['name' => 'Services techniques']);

        $created = $this->actingAs($admin)->postJson('/api/grievances', [
            'title' => 'Nid-de-poule dans la rue principale',
            'sector_id' => $sector->id,
            'department_id' => $department->id,
            'description' => 'La chaussée est dégradée.',
        ]);

        $created->assertCreated()->assertJsonPath('data.status', 'submitted');
        $grievanceId = $created->json('data.id');
        $this->assertDatabaseHas('grievances', ['id' => $grievanceId, 'status' => 'submitted']);
        $this->assertDatabaseHas('grievance_status_histories', ['grievance_id' => $grievanceId, 'status' => 'submitted', 'changed_by_id' => $admin->id]);
        $this->assertDatabaseHas('grievance_assignment_histories', ['grievance_id' => $grievanceId, 'department_id' => $department->id, 'changed_by_id' => $admin->id]);

        $this->actingAs($admin)->putJson("/api/grievances/{$grievanceId}", [
            'title' => 'Nid-de-poule traité',
            'sector_id' => $sector->id,
            'department_id' => null,
            'description' => 'La chaussée a été réparée.',
            'status' => 'processed',
            'complained_at' => now()->subDay()->toDateTimeString(),
        ])->assertOk()->assertJsonPath('data.status', 'processed');

        $this->assertDatabaseHas('grievance_status_histories', ['grievance_id' => $grievanceId, 'status' => 'processed', 'changed_by_id' => $admin->id]);
        $this->assertDatabaseHas('grievance_assignment_histories', ['grievance_id' => $grievanceId, 'department_id' => null, 'changed_by_id' => $admin->id]);
        $updated = \App\Models\Grievance::findOrFail($grievanceId);
        $this->assertNotNull($updated->due_at);
        $this->assertEquals(48 * 60, $updated->complained_at->diffInMinutes($updated->due_at));
    }

    public function test_due_date_must_be_after_the_complaint_date(): void
    {
        $admin = $this->makeAdmin();
        $sector = Sector::create(['name' => 'Environnement']);
        $date = now()->subHour()->toDateTimeString();

        $this->actingAs($admin)->postJson('/api/grievances', [
            'title' => 'Date invalide', 'sector_id' => $sector->id, 'description' => 'Test',
            'complained_at' => $date, 'due_at' => $date,
        ])->assertUnprocessable()->assertJsonValidationErrors('due_at');
    }

    public function test_a_regular_authenticated_user_can_manage_reference_data_and_grievances(): void
    {
        $user = User::factory()->create();

        $department = $this->actingAs($user)->postJson('/api/departments', ['name' => 'Accueil'])->assertCreated()->json('data');
        $sector = $this->actingAs($user)->postJson('/api/sectors', ['name' => 'Propreté'])->assertCreated()->json('data');

        $this->actingAs($user)->postJson('/api/grievances', [
            'title' => 'Poubelle pleine', 'sector_id' => $sector['id'], 'department_id' => $department['id'], 'description' => 'Demande citoyenne.',
        ])->assertCreated();
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $admin->roleAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);
        $admin->roleAssignments()->create(['role_id' => $role->id, 'assigned_at' => now()]);
        return $admin;
    }
}
