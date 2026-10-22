<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_read_update_and_delete_roles(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->postJson('/api/roles', [
            'name' => 'moderator',
            'description' => 'Can moderate meetings.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'moderator');

        $roleId = $response->json('data.id');

        $this->getJson("/api/roles/{$roleId}")
            ->assertOk()
            ->assertJsonPath('data.description', 'Can moderate meetings.');

        $this->putJson("/api/roles/{$roleId}", [
            'name' => 'meeting_moderator',
            'description' => 'Moderates meetings.',
        ])->assertOk()->assertJsonPath('data.name', 'meeting_moderator');

        $this->deleteJson("/api/roles/{$roleId}")->assertNoContent();
        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_role_with_assignment_history_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin();
        $client = User::factory()->create();
        $clientRole = Role::query()->where('name', 'client')->firstOrFail();

        $this->actingAs($admin)
            ->deleteJson("/api/roles/{$clientRole->id}")
            ->assertConflict();

        $this->assertDatabaseHas('roles', ['id' => $clientRole->id]);
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => $client->id, 'role_id' => $clientRole->id]);
    }

    public function test_builtin_roles_cannot_be_renamed_or_deleted(): void
    {
        $admin = $this->makeAdmin();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->putJson("/api/roles/{$adminRole->id}", ['name' => 'super_admin'])
            ->assertConflict();

        $this->deleteJson("/api/roles/{$adminRole->id}")->assertConflict();

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id, 'name' => 'admin']);
    }

    public function test_role_endpoints_require_authentication_but_are_available_to_clients(): void
    {
        $this->getJson('/api/roles')->assertUnauthorized();

        $client = User::factory()->create();

        $this->actingAs($client)->getJson('/api/roles')->assertOk()->assertJsonPath('meta.per_page', 10);

        $this->actingAs($client)->postJson('/api/roles', [
            'name' => 'meeting_observer',
            'description' => 'Can observe meetings.',
        ])->assertCreated()->assertJsonPath('data.name', 'meeting_observer');
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::query()->where('name', 'admin')->firstOrFail();

        $admin->roleAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);
        $admin->roleAssignments()->create([
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);

        return $admin;
    }
}
