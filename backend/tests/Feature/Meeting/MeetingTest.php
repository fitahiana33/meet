<?php

namespace Tests\Feature\Meeting;

use App\Models\Meeting;
use App\Models\MeetingJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_create_and_list_meetings(): void
    {
        $creator = User::factory()->create();

        $created = $this->actingAs($creator)->postJson('/api/meetings', [
            'title' => 'Product review',
            'description' => 'Review the next release.',
            'scheduled_at' => '2026-10-01 10:00:00',
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.title', 'Product review')
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.is_creator', true);

        $meetingId = $created->json('data.id');
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->getJson('/api/meetings')
            ->assertOk()
            ->assertJsonPath('data.0.id', $meetingId)
            ->assertJsonPath('data.0.creator.id', $creator->id)
            ->assertJsonPath('data.0.can_join', false);

        $this->assertDatabaseHas('meetings', [
            'id' => $meetingId,
            'creator_id' => $creator->id,
        ]);
        $this->assertNotEmpty(Meeting::query()->findOrFail($meetingId)->livekit_room_name);
    }

    public function test_join_requests_are_unique_and_creator_can_accept_them(): void
    {
        $creator = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = $this->createMeeting($creator);

        $request = $this->actingAs($participant)->postJson("/api/meetings/{$meeting->id}/join-requests");
        $request->assertCreated()->assertJsonPath('data.status', 'pending');

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertConflict();

        $this->actingAs($participant)
            ->getJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertForbidden();

        $requestId = $request->json('data.id');
        $this->actingAs($creator)
            ->getJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertOk()
            ->assertJsonPath('data.0.user.id', $participant->id);

        $this->actingAs($creator)
            ->patchJson("/api/meetings/{$meeting->id}/join-requests/{$requestId}", ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertConflict();

        $this->actingAs($participant)
            ->getJson("/api/meetings/{$meeting->id}")
            ->assertOk()
            ->assertJsonPath('data.my_request.status', 'accepted')
            ->assertJsonPath('data.can_join', false);

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/start")
            ->assertOk()
            ->assertJsonPath('data.status', 'live');

        $this->actingAs($participant)
            ->getJson("/api/meetings/{$meeting->id}")
            ->assertOk()
            ->assertJsonPath('data.can_join', true);
    }

    public function test_creator_manages_meeting_without_becoming_an_invited_participant(): void
    {
        $creator = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = $this->createMeeting($creator);

        $selfRequest = MeetingJoinRequest::query()->create([
            'meeting_id' => $meeting->id,
            'user_id' => $creator->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertUnprocessable();

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertCreated();

        $this->actingAs($creator)
            ->getJson('/api/meetings')
            ->assertOk()
            ->assertJsonPath('data.0.is_creator', true)
            ->assertJsonPath('data.0.can_join', false)
            ->assertJsonPath('data.0.can_start', true)
            ->assertJsonPath('data.0.can_host', false)
            ->assertJsonPath('data.0.my_request', null)
            ->assertJsonPath('data.0.participants_count', 1);

        $this->actingAs($creator)
            ->getJson("/api/meetings/{$meeting->id}/join-requests")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user.id', $participant->id);

        $this->actingAs($creator)
            ->patchJson("/api/meetings/{$meeting->id}/join-requests/{$selfRequest->id}", ['status' => 'cancelled'])
            ->assertNotFound();
    }

    public function test_livekit_token_requires_creator_or_accepted_participant(): void
    {
        config([
            'services.livekit.url' => 'ws://localhost:7880',
            'services.livekit.api_key' => 'devkey',
            'services.livekit.api_secret' => 'test-livekit-secret-at-least-32-bytes-long',
        ]);

        $creator = User::factory()->create();
        $participant = User::factory()->create();
        $outsider = User::factory()->create();
        $meeting = $this->createMeeting($creator);

        $this->actingAs($outsider)
            ->postJson("/api/meetings/{$meeting->id}/access-token")
            ->assertForbidden();

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/access-token")
            ->assertForbidden();

        $joinRequest = $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/join-requests")
            ->json('data.id');

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/access-token")
            ->assertForbidden();

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/start")
            ->assertForbidden();

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/start")
            ->assertOk()
            ->assertJsonPath('data.status', 'live')
            ->assertJsonPath('data.can_start', false)
            ->assertJsonPath('data.can_host', true);

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/access-token")
            ->assertOk()
            ->assertJsonStructure(['server_url', 'participant_token', 'room_name']);

        $this->actingAs($creator)->patchJson(
            "/api/meetings/{$meeting->id}/join-requests/{$joinRequest}",
            ['status' => 'accepted'],
        )->assertOk();

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/access-token")
            ->assertOk()
            ->assertJsonPath('meeting_id', $meeting->id);
    }

    public function test_only_the_creator_can_end_a_meeting(): void
    {
        $creator = User::factory()->create();
        $participant = User::factory()->create();
        $meeting = $this->createMeeting($creator);

        $this->actingAs($participant)
            ->postJson("/api/meetings/{$meeting->id}/end")
            ->assertForbidden();

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/start")
            ->assertOk();

        $this->actingAs($creator)
            ->postJson("/api/meetings/{$meeting->id}/end")
            ->assertOk()
            ->assertJsonPath('data.status', 'ended');
    }

    private function createMeeting(User $creator): Meeting
    {
        return Meeting::query()->create([
            'creator_id' => $creator->id,
            'title' => 'Team sync',
            'description' => 'Weekly meeting.',
            'status' => 'scheduled',
            'livekit_room_name' => 'meeting-test-'.$creator->id.'-'.uniqid(),
        ]);
    }
}
