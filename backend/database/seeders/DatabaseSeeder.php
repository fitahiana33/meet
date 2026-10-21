<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $assignRole = static function (User $user, Role $role): void {
            $currentAssignment = $user->roleAssignments()->whereNull('ended_at')->first();

            if ($currentAssignment === null || (int) $currentAssignment->role_id !== (int) $role->id) {
                $currentAssignment?->update(['ended_at' => now()]);

                $user->roleAssignments()->create([
                    'role_id' => $role->id,
                    'assigned_at' => now(),
                ]);
            }
        };

        $clientRole = Role::query()->where('name', 'client')->firstOrFail();

        $testUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
        $assignRole($testUser, $clientRole);

        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'password' => 'admin123',
                'email_verified_at' => now(),
            ],
        );

        $assignRole($admin, $adminRole);
    }
}
