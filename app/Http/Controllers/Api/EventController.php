<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class EventController extends Controller implements HasMiddleware
{
    use CanLoadRelationships;

    private array $relations = [
        'user',
        'attendees',
        'attendees.user'
    ];

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    public function index()
    {
        Gate::authorize('viewAny', Event::class);

        $query = $this->loadRelationships(Event::query());
        return EventResource::collection($query->latest()->paginate());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Event::class);

        $validated = $request->validate([
            'name' => 'required|string|min:5|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $event = Event::create([
            ...$validated,
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    public function update(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'name' => 'sometimes|string|min:5|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        $event->update($validated);

        return new EventResource($this->loadRelationships($event));
    }

    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);

        $event->delete();

        return response(status: 204);
    }
}
