<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Event\EventService;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function today()
    {
        $today = Carbon::now()
            ->timezone(config('tracking.timezone'))
            ->toDateString();

        return response()->json($this->eventService->todayStats($today));
    }
}
