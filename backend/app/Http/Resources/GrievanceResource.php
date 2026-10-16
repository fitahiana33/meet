<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrievanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'sector' => $this->whenLoaded('sector', fn () => ['id' => $this->sector->id, 'name' => $this->sector->name]),
            'department' => $this->whenLoaded('department', fn () => $this->department ? ['id' => $this->department->id, 'name' => $this->department->name] : null),
            'complained_at' => $this->complained_at?->toISOString(),
            'due_at' => $this->due_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'documents' => $this->whenLoaded('documents', fn () => $this->documents->map(fn ($document) => [
                'id' => $document->id,
                'name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size' => $document->size,
                // Build the link from the current API request so the backend port is preserved in local development.
                'url' => url('/storage/'.ltrim($document->path, '/')),
            ])),
            'status_history' => $this->whenLoaded('statusHistories', fn () => $this->statusHistories->map(fn ($history) => [
                'id' => $history->id,
                'status' => $history->status,
                'changed_at' => $history->changed_at?->toISOString(),
                'changed_by' => $history->user ? ['id' => $history->user->id, 'name' => $history->user->displayName()] : null,
            ])),
            'assignment_history' => $this->whenLoaded('assignmentHistories', fn () => $this->assignmentHistories->map(fn ($history) => [
                'id' => $history->id,
                'department' => $history->department ? ['id' => $history->department->id, 'name' => $history->department->name] : null,
                'changed_at' => $history->changed_at?->toISOString(),
                'changed_by' => $history->user ? ['id' => $history->user->id, 'name' => $history->user->displayName()] : null,
            ])),
        ];
    }
}
