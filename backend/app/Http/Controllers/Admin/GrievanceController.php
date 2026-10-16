<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGrievanceRequest;
use App\Http\Requests\Admin\UpdateGrievanceRequest;
use App\Http\Resources\GrievanceResource;
use App\Models\Grievance;
use App\Models\GrievanceDocument;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Grievance::query()
            ->with(['sector', 'department', 'documents'])
            ->orderByDesc('complained_at');

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        }
        if ($status = $request->query('status')) $query->where('status', $status);
        if ($sectorId = $request->query('sector_id')) $query->where('sector_id', $sectorId);

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        return GrievanceResource::collection($query->paginate($perPage));
    }

    public function store(StoreGrievanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $complainedAt = isset($data['complained_at']) ? Carbon::parse($data['complained_at']) : now();
        $data['complained_at'] = $complainedAt;
        $data['due_at'] = isset($data['due_at']) ? Carbon::parse($data['due_at']) : $complainedAt->copy()->addHours(48);
        $data['status'] = $data['status'] ?? 'submitted';
        unset($data['documents']);

        $grievance = DB::transaction(function () use ($request, $data): Grievance {
            $grievance = Grievance::create([...$data, 'created_by_id' => $request->user()->id]);
            $this->recordStatusChange($grievance, $request->user()->id);
            if ($grievance->department_id) $this->recordAssignmentChange($grievance, $request->user()->id);
            $this->storeDocuments($grievance, $request);
            return $grievance;
        });

        return (new GrievanceResource($this->loadGrievance($grievance)))->response()->setStatusCode(201);
    }

    public function show(Grievance $grievance): GrievanceResource
    {
        return new GrievanceResource($this->loadGrievance($grievance));
    }

    public function update(UpdateGrievanceRequest $request, Grievance $grievance): GrievanceResource
    {
        $data = $request->validated();
        $oldStatus = $grievance->status;
        $oldDepartment = $grievance->department_id;
        $complainedAt = Carbon::parse($data['complained_at']);
        $data['complained_at'] = $complainedAt;
        $data['due_at'] = isset($data['due_at']) ? Carbon::parse($data['due_at']) : $complainedAt->copy()->addHours(48);
        unset($data['documents']);

        DB::transaction(function () use ($request, $grievance, $data, $oldStatus, $oldDepartment): void {
            $grievance->update($data);
            if ($oldStatus !== $grievance->status) $this->recordStatusChange($grievance, $request->user()->id);
            if ((int) $oldDepartment !== (int) $grievance->department_id) $this->recordAssignmentChange($grievance, $request->user()->id);
            $this->storeDocuments($grievance, $request);
        });

        return new GrievanceResource($this->loadGrievance($grievance->refresh()));
    }

    public function destroy(Grievance $grievance): JsonResponse
    {
        $grievance->load('documents');
        foreach ($grievance->documents as $document) Storage::disk('public')->delete($document->path);
        $grievance->delete();
        return response()->json([], 204);
    }

    public function destroyDocument(Grievance $grievance, GrievanceDocument $document): JsonResponse
    {
        abort_unless($document->grievance_id === $grievance->id, 404);
        Storage::disk('public')->delete($document->path);
        $document->delete();
        return response()->json([], 204);
    }

    private function loadGrievance(Grievance $grievance): Grievance
    {
        return $grievance->load([
            'sector', 'department', 'documents',
            'statusHistories.user', 'assignmentHistories.department', 'assignmentHistories.user',
        ]);
    }

    private function storeDocuments(Grievance $grievance, Request $request): void
    {
        foreach ($request->file('documents', []) as $file) {
            $grievance->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('grievances', 'public'),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function recordStatusChange(Grievance $grievance, int $userId): void
    {
        $grievance->statusHistories()->create(['status' => $grievance->status, 'changed_by_id' => $userId, 'changed_at' => now()]);
    }

    private function recordAssignmentChange(Grievance $grievance, int $userId): void
    {
        $grievance->assignmentHistories()->create(['department_id' => $grievance->department_id, 'changed_by_id' => $userId, 'changed_at' => now()]);
    }
}
