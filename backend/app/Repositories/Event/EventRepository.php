<?php

namespace App\Repositories\Event;

use App\Interfaces\Event\EventRepositoryInterface;
use App\Models\Event;

class EventRepository implements EventRepositoryInterface
{

    public function create(array $data): mixed
    {
        return Event::create($data);
    }

    public function findByIdempotencyKey(string $key): ?object
    {
        return Event::where('idempotency_key', $key)->first();
    }

    public function getTodayStats(string $today): array
    {
        return Event::whereDate('ts', $today)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
