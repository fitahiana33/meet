<?php

namespace App\Http\Controllers\Meeting;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Meeting\StoreMeetingRequest;
use App\Http\Requests\Meeting\UpdateJoinRequestRequest;
use App\Http\Resources\MeetingJoinRequestResource;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingJoinRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::query()
            ->with('creator')
            ->with(['joinRequests' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->withCount(['joinRequests as accepted_join_requests_count' => fn ($query) => $query
                ->where('status', 'accepted')
                ->whereColumn('meeting_join_requests.user_id', '<>', 'meetings.creator_id')])
            ->whereNotIn('status', ['cancelled'])
            ->latest();

        $perPage = min(max((int) $request->query('per_page', 12), 1), 50);

        return MeetingResource::collection($query->paginate($perPage));
    }

    public function store(StoreMeetingRequest $request): JsonResponse
    {
        $meeting = Meeting::query()->create([
            ...$request->validated(),
            'creator_id' => $request->user()->id,
            'status' => 'scheduled',
            'livekit_room_name' => 'meeting-'.Str::uuid(),
        ]);

        return (new MeetingResource($meeting->load('creator')))->response()->setStatusCode(201);
    }

    public function show(Request $request, Meeting $meeting): MeetingResource
    {
        return new MeetingResource($meeting->load([
            'creator',
            'joinRequests' => fn ($query) => $query->where('user_id', $request->user()->id),
        ])->loadCount([
            'joinRequests as accepted_join_requests_count' => fn ($query) => $query
                ->where('status', 'accepted')
                ->whereColumn('meeting_join_requests.user_id', '<>', 'meetings.creator_id'),
        ]));
    }

    public function requestJoin(Request $request, Meeting $meeting): JsonResponse
    {
        if ((int) $meeting->creator_id === (int) $request->user()->id) {
            return response()->json(['message' => 'The meeting creator does not need to request access.'], 422);
        }

        if (in_array($meeting->status, ['ended', 'cancelled'], true)) {
            return response()->json(['message' => 'This meeting is no longer accepting participants.'], 409);
        }

        $joinRequest = DB::transaction(function () use ($request, $meeting): MeetingJoinRequest {
            $existing = MeetingJoinRequest::query()
                ->where('meeting_id', $meeting->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($existing?->status === 'pending') {
                abort(409, 'A join request is already pending.');
            }

            if ($existing?->status === 'accepted') {
                abort(409, 'This user has already been accepted.');
            }

            if ($existing !== null) {
                $existing->update(['status' => 'pending', 'responded_at' => null]);

                return $existing->fresh();
            }

            return MeetingJoinRequest::query()->create([
                'meeting_id' => $meeting->id,
                'user_id' => $request->user()->id,
                'status' => 'pending',
            ]);
        });

        return (new MeetingJoinRequestResource($joinRequest->load('user')))->response()->setStatusCode(201);
    }

    public function start(Request $request, Meeting $meeting): MeetingResource
    {
        $this->ensureCreator($request, $meeting);

        abort_unless($meeting->status === 'scheduled', 409, 'Only a scheduled meeting can be started.');

        $meeting->update([
            'status' => 'live',
            'started_at' => now(),
        ]);

        return new MeetingResource($meeting->fresh()->load('creator')->loadCount([
            'joinRequests as accepted_join_requests_count' => fn ($query) => $query
                ->where('status', 'accepted')
                ->whereColumn('meeting_join_requests.user_id', '<>', 'meetings.creator_id'),
        ]));
    }

    public function requests(Request $request, Meeting $meeting)
    {
        $this->ensureCreator($request, $meeting);

        return MeetingJoinRequestResource::collection(
            $meeting->joinRequests()
                ->where('user_id', '<>', $meeting->creator_id)
                ->with('user')
                ->latest()
                ->paginate(30),
        );
    }

    public function updateRequest(
        UpdateJoinRequestRequest $request,
        Meeting $meeting,
        MeetingJoinRequest $joinRequest,
    ): MeetingJoinRequestResource {
        $this->ensureCreator($request, $meeting);

        abort_unless((int) $joinRequest->meeting_id === (int) $meeting->id, 404);
        abort_if((int) $joinRequest->user_id === (int) $meeting->creator_id, 404);

        if ($joinRequest->status !== 'pending') {
            abort(409, 'This join request has already been processed.');
        }

        $joinRequest->update([
            'status' => $request->validated('status'),
            'responded_at' => now(),
        ]);

        return new MeetingJoinRequestResource($joinRequest->load('user'));
    }

    public function accessToken(Request $request, Meeting $meeting): JsonResponse
    {
        $user = $request->user();
        $isCreator = (int) $meeting->creator_id === (int) $user->id;
        $isAccepted = $meeting->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->exists();

        abort_unless($meeting->canBeJoined() && ($isCreator || $isAccepted), 403, 'You are not allowed to join this meeting.');

        $apiKey = (string) config('services.livekit.api_key');
        $apiSecret = (string) config('services.livekit.api_secret');
        $serverUrl = (string) config('services.livekit.url');

        if ($apiKey === '' || $apiSecret === '' || $serverUrl === '') {
            return response()->json(['message' => 'LiveKit is not configured on the server.'], 503);
        }

        $options = (new AccessTokenOptions)
            ->setIdentity((string) $user->id)
            ->setName($user->displayName())
            ->setTtl(2 * 60 * 60);
        $grant = (new VideoGrant)
            ->setRoomJoin()
            ->setRoomName($meeting->livekit_room_name)
            ->setCanPublish()
            ->setCanSubscribe()
            ->setCanPublishData();
        $token = (new AccessToken($apiKey, $apiSecret))
            ->init($options)
            ->setGrant($grant)
            ->toJwt();

        return response()->json([
            'server_url' => $serverUrl,
            'participant_token' => $token,
            'room_name' => $meeting->livekit_room_name,
            'meeting_id' => $meeting->id,
        ]);
    }

    public function end(Request $request, Meeting $meeting): MeetingResource
    {
        $this->ensureCreator($request, $meeting);

        abort_unless($meeting->status === 'live', 409, 'Only a live meeting can be ended.');

        $meeting->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return new MeetingResource($meeting->fresh()->load('creator'));
    }

    private function ensureCreator(Request $request, Meeting $meeting): void
    {
        abort_unless((int) $meeting->creator_id === (int) $request->user()->id, 403, 'Only the meeting creator can manage it.');
    }
}
