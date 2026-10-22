<?php

namespace Tests\Feature\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_read_update_and_delete_users(): void
    {
        $admin = $this->makeAdmin();
        $clientRole = Role::query()->where('name', 'client')->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'Amina',
            'last_name' => 'Rakoto',
            'email' => 'amina@example.test',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
            'role_id' => $clientRole->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.first_name', 'Amina')
            ->assertJsonPath('data.last_name', 'Rakoto')
            ->assertJsonPath('data.role.name', 'client')
            ->assertJsonMissingPath('data.password');

        $userId = $response->json('data.id');
        $user = User::query()->findOrFail($userId);
        $this->assertTrue(Hash::check('secure-pass-123', $user->password));

        $this->getJson("/api/users/{$userId}")
            ->assertOk()
            ->assertJsonPath('data.email', 'amina@example.test');

        $this->getJson('/api/users?search=amina')
            ->assertOk()
            ->assertJsonPath('data.0.id', $userId);

        $this->patchJson("/api/users/{$userId}", [
            'first_name' => 'Mina',
            'role_id' => $adminRole->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Mina')
            ->assertJsonPath('data.role.name', 'admin');

        $this->assertDatabaseCount('user_role_assignments', 4);
        $this->assertNotNull($user->roleAssignments()->where('role_id', $clientRole->id)->firstOrFail()->ended_at);

        $this->deleteJson("/api/users/{$userId}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_endpoints_require_an_admin(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();

        $client = User::factory()->create();

        $this->actingAs($client)->getJson('/api/users')->assertForbidden();
    }

    public function test_user_list_is_paginated_by_ten_by_default(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(11)->create();

        $this->actingAs($admin)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_user_creation_validates_required_fields_and_unique_email(): void
    {
        $admin = $this->makeAdmin();
        $clientRole = Role::query()->where('name', 'client')->firstOrFail();

        $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'Amina',
            'last_name' => 'Rakoto',
            'email' => $admin->email,
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
            'role_id' => $clientRole->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
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
