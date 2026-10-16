<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectorRequest;
use App\Http\Requests\Admin\UpdateSectorRequest;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;

class SectorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Sector::query()->withCount('grievances')->orderBy('name')->get()]);
    }

    public function store(StoreSectorRequest $request): JsonResponse
    {
        return response()->json(['data' => Sector::create($request->validated())], 201);
    }

    public function show(Sector $sector): JsonResponse { return response()->json(['data' => $sector->loadCount('grievances')]); }

    public function update(UpdateSectorRequest $request, Sector $sector): JsonResponse
    {
        $sector->update($request->validated());
        return response()->json(['data' => $sector->refresh()->loadCount('grievances')]);
    }

    public function destroy(Sector $sector): JsonResponse
    {
        if ($sector->grievances()->exists()) {
            return response()->json(['message' => 'Ce secteur est utilisé par une ou plusieurs doléances.'], 409);
        }
        $sector->delete();
        return response()->json([], 204);
    }
}
