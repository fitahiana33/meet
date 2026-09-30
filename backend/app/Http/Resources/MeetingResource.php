<?php

namespace App\Http\Resources;

use App\Models\MeetingJoinRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isCreator = (int) $this->creator_id === (int) $request->user()?->id;
        $myRequest = ! $isCreator && $this->relationLoaded('joinRequests')
            ? $this->joinRequests->first()
            : null;
        $acceptedCount = (int) ($this->accepted_join_requests_count ?? 0);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'scheduled_at' => $this->scheduled_at,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'status' => $this->status,
            'creator' => $this->whenLoaded('creator', fn (): array => [
                'id' => $this->creator->id,
                'first_name' => $this->creator->first_name,
                'last_name' => $this->creator->last_name,
                'name' => $this->creator->displayName(),
            ]),
            'participants_count' => 1 + $acceptedCount,
            'is_creator' => $isCreator,
            'my_request' => $myRequest instanceof MeetingJoinRequest ? [
                'id' => $myRequest->id,
                'status' => $myRequest->status,
                'created_at' => $myRequest->created_at,
                'responded_at' => $myRequest->responded_at,
            ] : null,
            'can_join' => ! $isCreator && $this->canBeJoined() && $myRequest?->status === 'accepted',
            'can_start' => $isCreator && $this->status === 'scheduled',
            'can_host' => $isCreator && $this->canBeJoined(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function canBeJoined(): bool
    {
        return $this->status === 'live';
    }
}
