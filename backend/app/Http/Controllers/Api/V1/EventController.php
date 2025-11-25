<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Services\Event\EventService;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function store(StoreEventRequest $request)
    {
        $result = $this->eventService->storeEvent($request->validated());

        if ($result['duplicate']) {
            return response()->json(['duplicate' => true]);
        }

        return response()->json([
            'status' => 'created',
        ], 201);
    }
}
