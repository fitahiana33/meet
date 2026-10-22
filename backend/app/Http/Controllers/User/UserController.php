<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('currentRoleAssignment.role')->orderBy('id');
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        return UserResource::collection($query->paginate($perPage));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $roleId = (int) $validated['role_id'];
        unset($validated['role_id']);

        $user = DB::transaction(function () use ($validated, $roleId): User {
            $user = User::create($validated);
            $this->assignCurrentRole($user, $roleId);

            return $user->load('currentRoleAssignment.role');
        });

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('currentRoleAssignment.role'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $validated = $request->validated();
        $roleId = isset($validated['role_id']) ? (int) $validated['role_id'] : null;
        unset($validated['role_id']);

        $user = DB::transaction(function () use ($validated, $roleId, $user): User {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $user->update($validated);

            if ($roleId !== null) {
                $this->assignCurrentRole($user, $roleId);
            }

            return $user->load('currentRoleAssignment.role');
        });

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->noContent();
    }

    private function assignCurrentRole(User $user, int $roleId): void
    {
        $activeAssignments = $user->roleAssignments()->whereNull('ended_at')->orderByDesc('id')->get();

        if ($activeAssignments->count() === 1 && (int) $activeAssignments->first()->role_id === $roleId) {
            return;
        }

        if ($activeAssignments->isNotEmpty()) {
            $user->roleAssignments()->whereNull('ended_at')->update([
                'ended_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $user->roleAssignments()->create([
            'role_id' => $roleId,
            'assigned_at' => now(),
        ]);
    }
}
