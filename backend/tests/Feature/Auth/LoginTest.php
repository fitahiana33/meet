<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_use_admin_endpoints_with_the_bearer_token(): void
    {
        $admin = $this->userWithRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $admin->id)
            ->assertJsonPath('user.role.name', 'admin');

        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk();

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.role.name', 'admin');
    }

    public function test_client_can_login_but_is_forbidden_from_admin_endpoints(): void
    {
        $client = $this->userWithRole('client');

        $response = $this->postJson('/api/login', [
            'email' => $client->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.role.name', 'client');

        $this->withToken($response->json('token'))
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_login_rejects_invalid_credentials_and_invalid_input(): void
    {
        $user = $this->userWithRole('client');

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnauthorized();

        $this->postJson('/api/login', [
            'email' => 'not-an-email',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $client = $this->userWithRole('client');
        $login = $this->postJson('/api/login', [
            'email' => $client->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_api_authentication_errors_are_json_even_without_accept_header(): void
    {
        $this->get('/api/me')
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('name', $roleName)->firstOrFail();

        $user->roleAssignments()->whereNull('ended_at')->update(['ended_at' => now()]);
        $user->roleAssignments()->create([
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);

        return $user;
    }
}
