<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EntityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Entity::query()->withCount('grievances')->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $entity = Entity::create($this->validatedData($request));

        return response()->json(['data' => $entity], 201);
    }

    public function show(Entity $entity): JsonResponse
    {
        return response()->json(['data' => $entity->loadCount('grievances')]);
    }

    public function update(Request $request, Entity $entity): JsonResponse
    {
        $entity->update($this->validatedData($request, $entity));

        return response()->json(['data' => $entity->refresh()->loadCount('grievances')]);
    }

    public function destroy(Entity $entity): JsonResponse
    {
        if ($entity->grievances()->exists()) {
            return response()->json(['message' => 'Cette entité est utilisée par une ou plusieurs doléances.'], 409);
        }

        $entity->delete();

        return response()->json([], 204);
    }

    private function validatedData(Request $request, ?Entity $entity = null): array
    {
        $titleRule = Rule::unique('entities', 'title');
        if ($entity) {
            $titleRule->ignore($entity->id);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:180', $titleRule],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
