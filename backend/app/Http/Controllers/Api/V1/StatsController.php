<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Event\EventStatsResource;
use App\Services\Event\EventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function today(): JsonResponse
    {
        $today = Carbon::now()
            ->timezone(config('tracking.timezone'))
            ->toDateString();

        $stats = $this->eventService->todayStats($today);

        return new EventStatsResource($stats)
            ->response();
    }
}
