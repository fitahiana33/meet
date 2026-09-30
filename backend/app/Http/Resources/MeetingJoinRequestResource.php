<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingJoinRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'name' => $this->user->displayName(),
                'email' => $this->user->email,
            ]),
            'created_at' => $this->created_at,
            'responded_at' => $this->responded_at,
        ];
    }
}
