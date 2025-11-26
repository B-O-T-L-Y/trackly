<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Resources\Event\EventResource;
use App\Services\Event\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function store(StoreEventRequest $request): JsonResponse
    {
        $result = $this->eventService->storeEvent($request->validated());

        if ($result['duplicate']) {
            return new EventResource($result['event'])
                ->additional(['duplicate' => true])
                ->response();
        }

        return new EventResource($result['event'])
            ->additional(['status' => 'created'])
            ->response()
            ->setStatusCode(201);
    }
}
