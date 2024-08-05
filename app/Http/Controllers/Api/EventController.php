<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = [
        'user',
        'attendees',
        'attendees.user'
    ];

    public function index()
    {
        $query = $this->loadRelationships(Event::query());
        return EventResource::collection($query->latest()->paginate());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:5|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $event = Event::create([
            ...$validated,
            'user_id' => 1
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|min:5|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        $updated = $event->update($validated); // bool - check if update worked

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204); // no response
    }
}
