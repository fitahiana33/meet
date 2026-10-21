<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGrievanceRequest;
use App\Http\Requests\Admin\UpdateGrievanceRequest;
use App\Http\Resources\GrievanceResource;
use App\Models\Declarant;
use App\Models\Grievance;
use App\Models\GrievanceDocument;
use App\Models\Sector;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GrievanceController extends Controller
{
    public function index(Request $request)
    {
        $this->validateFilters($request);
        $query = $this->filteredQuery($request)->with(['sector', 'entity', 'department', 'documents', 'declarants'])
            ->orderByDesc('complained_at');
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        return GrievanceResource::collection($query->paginate($perPage));
    }

    public function dashboard(Request $request): JsonResponse
    {
        $this->validateFilters($request);
        $rows = $this->filteredQuery($request)->get(['status', 'due_at', 'complained_at', 'sector_id']);
        $statusCounts = collect(Grievance::STATUSES)->mapWithKeys(fn (string $status) => [
            $status => $rows->where('status', $status)->count(),
        ]);
        $monthKeys = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset)->format('Y-m'));
        $monthlyCounts = $rows->groupBy(fn ($row) => Carbon::parse($row->complained_at)->format('Y-m'));
        $monthly = $monthKeys->map(fn (string $month) => [
            'month' => $month,
            'label' => Carbon::createFromFormat('Y-m', $month)->locale('fr')->translatedFormat('M'),
            'count' => $monthlyCounts->get($month, collect())->count(),
        ]);
        $sectorCounts = $rows->groupBy('sector_id')->map(fn ($items) => $items->count());
        $sectors = Sector::query()->whereIn('id', $sectorCounts->keys())->get(['id', 'name'])
            ->map(fn ($sector) => ['name' => $sector->name, 'count' => $sectorCounts->get($sector->id, 0)])
            ->sortByDesc('count')
            ->take(8)
            ->values();

        return response()->json(['data' => [
            'total' => $rows->count(),
            'overdue' => $rows->filter(fn ($row) => ! in_array($row->status, ['processed', 'rejected'], true)
                && $row->due_at
                && Carbon::parse($row->due_at)->isPast())->count(),
            'processed' => $statusCounts->get('processed', 0),
            'in_progress' => $statusCounts->get('in_progress', 0),
            'by_status' => $statusCounts,
            'monthly' => $monthly,
            'by_sector' => $sectors,
        ]]);
    }

    public function store(StoreGrievanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $complainedAt = isset($data['complained_at']) ? Carbon::parse($data['complained_at']) : now();
        $data['complained_at'] = $complainedAt;
        $data['due_at'] = isset($data['due_at']) ? Carbon::parse($data['due_at']) : $complainedAt->copy()->addHours(48);
        $data['status'] = $data['status'] ?? 'submitted';
        unset($data['documents'], $data['declarants'], $data['declarants_present']);

        $grievance = DB::transaction(function () use ($request, $data): Grievance {
            $grievance = Grievance::create([...$data, 'created_by_id' => $request->user()->id]);
            $this->recordStatusChange($grievance, $request->user()->id);
            if ($grievance->department_id) {
                $this->recordAssignmentChange($grievance, $request->user()->id);
            }
            $this->storeDocuments($grievance, $request);
            $this->syncDeclarants($grievance, $request->validated('declarants', []));

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
        $declarantsProvided = ! empty($data['declarants_present']) || array_key_exists('declarants', $data);
        $complainedAt = Carbon::parse($data['complained_at']);
        $data['complained_at'] = $complainedAt;
        $data['due_at'] = isset($data['due_at']) ? Carbon::parse($data['due_at']) : $complainedAt->copy()->addHours(48);
        unset($data['documents'], $data['declarants'], $data['declarants_present']);

        DB::transaction(function () use ($request, $grievance, $data, $oldStatus, $oldDepartment, $declarantsProvided): void {
            $grievance->update($data);
            if ($oldStatus !== $grievance->status) {
                $this->recordStatusChange($grievance, $request->user()->id);
            }
            if ((int) $oldDepartment !== (int) $grievance->department_id) {
                $this->recordAssignmentChange($grievance, $request->user()->id);
            }
            $this->storeDocuments($grievance, $request);
            if ($declarantsProvided) {
                $this->syncDeclarants($grievance, $request->validated('declarants', []));
            }
        });

        return new GrievanceResource($this->loadGrievance($grievance->refresh()));
    }

    public function destroy(Grievance $grievance): JsonResponse
    {
        $grievance->load('documents');
        foreach ($grievance->documents as $document) {
            Storage::disk('public')->delete($document->path);
        }
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
            'sector', 'entity', 'department', 'declarants', 'documents',
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

    private function syncDeclarants(Grievance $grievance, array $entries): void
    {
        $ids = [];

        foreach ($entries as $entry) {
            if (! empty($entry['id'])) {
                $ids[] = (int) $entry['id'];

                continue;
            }

            $declarant = Declarant::create([
                'first_name' => $entry['first_name'],
                'last_name' => $entry['last_name'],
                'email' => $entry['email'] ?? null,
                'address' => $entry['address'] ?? null,
                'phone' => $entry['phone'] ?? null,
            ]);

            $photo = $entry['photo'] ?? null;
            if ($photo instanceof UploadedFile) {
                $path = Storage::disk('local')->putFile('declarants', $photo);
                if ($path === false) {
                    throw new RuntimeException('La photo du déclarant n’a pas pu être enregistrée.');
                }
                $declarant->update(['photo_path' => $path]);
            }

            $ids[] = $declarant->id;
        }

        $grievance->declarants()->sync(array_values(array_unique($ids)));
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:'.implode(',', Grievance::STATUSES)],
            'sector_id' => ['nullable', 'integer', 'exists:sectors,id'],
            'entity_id' => ['nullable', 'integer', 'exists:entities,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Grievance::query();

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(fn (Builder $builder) => $builder
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('declarants', fn (Builder $declarants) => $declarants
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
        }

        foreach (['status', 'sector_id', 'entity_id', 'department_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('complained_at', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('complained_at', '<=', $request->query('date_to'));
        }

        return $query;
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
