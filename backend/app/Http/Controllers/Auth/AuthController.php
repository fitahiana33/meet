<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()
            ->with('currentRoleAssignment.role')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($credentials['email'])])
            ->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        $role = $user->currentRoleAssignment?->role;

        if ($role === null || ! in_array($role->name, ['admin', 'client'], true)) {
            return response()->json(['message' => 'This account does not have an allowed role.'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('currentRoleAssignment.role');

        return response()->json(['user' => $this->userData($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    private function userData(User $user): array
    {
        $role = $user->currentRoleAssignment?->role;

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => [
                'id' => $role?->id,
                'name' => $role?->name,
            ],
        ];
    }
}
