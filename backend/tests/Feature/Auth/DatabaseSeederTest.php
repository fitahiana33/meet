<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_configured_admin_account_idempotently(): void
    {
        Artisan::call('db:seed');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertSame('admin', $admin->currentRoleAssignment?->role?->name);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ])->assertOk()
            ->assertJsonPath('user.role.name', 'admin');

        Artisan::call('db:seed');

        $this->assertDatabaseCount('users', 2);
        $this->assertSame(
            1,
            $admin->roleAssignments()->whereNull('ended_at')->count(),
        );

        $this->withToken($login->json('token'))
            ->getJson('/api/users')
            ->assertOk();
    }
}
