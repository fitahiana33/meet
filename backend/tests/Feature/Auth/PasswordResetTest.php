<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_reset_link_and_reset_password_with_it(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createToken('existing-session');
        $token = null;

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$token, $user): bool {
                $token = $notification->token;

                return str_starts_with(
                    $notification->toMail($user)->actionUrl,
                    'http://localhost:5173/reset-password?',
                );
            },
        );

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'another-secure-password',
            'password_confirmation' => 'another-secure-password',
        ])->assertUnprocessable();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'new-secure-password',
        ])->assertOk();
    }

    public function test_reset_link_request_does_not_reveal_unknown_email_addresses(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'unknown@example.com',
        ])->assertOk();

        $this->assertSame(
            'Si cette adresse est associée à un compte, un lien de réinitialisation vient d’être envoyé.',
            $response->json('message'),
        );

        Notification::assertNothingSent();
    }
}
