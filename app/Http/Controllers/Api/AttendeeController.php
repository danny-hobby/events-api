<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AttendeeController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;

    private array $relations = ['user'];

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    public function store(Request $request, Event $event)
    {
        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => 1
            ])
        );

        return new AttendeeResource($attendee);
    }

    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }

    public function destroy(string $event, Attendee $attendee)
    {
        Gate::authorize('delete', $attendee);

        $attendee->delete();

        return response()->json(status: 204);
    }
}
