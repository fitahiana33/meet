<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        return RoleResource::collection(Role::query()->orderBy('name')->paginate($perPage));
    }

    public function store(StoreRoleRequest $request)
    {
        return (new RoleResource(Role::create($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse|RoleResource
    {
        $validated = $request->validated();

        if (in_array($role->name, ['admin', 'client'], true)
            && isset($validated['name'])
            && $validated['name'] !== $role->name) {
            return response()->json([
                'message' => 'Built-in role names cannot be changed.',
            ], 409);
        }

        $role->update($validated);

        return new RoleResource($role->refresh());
    }

    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['admin', 'client'], true)) {
            return response()->json([
                'message' => 'Built-in roles cannot be deleted.',
            ], 409);
        }

        if ($role->userRoleAssignments()->exists()) {
            return response()->json([
                'message' => 'A role with assignment history cannot be deleted.',
            ], 409);
        }

        $role->delete();

        return response()->json([], 204);
    }
}
