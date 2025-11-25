<?php

namespace App\Interfaces\Event;

interface EventRepositoryInterface
{
    public function create(array $data): mixed;

    public function findByIdempotencyKey(string $key): ?object;

    public function getTodayStats(string $today): array;
}
