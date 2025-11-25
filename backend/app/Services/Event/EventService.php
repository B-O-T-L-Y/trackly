<?php

namespace App\Services\Event;

use App\Interfaces\Event\EventRepositoryInterface;

class EventService
{
    public function __construct(
        protected EventRepositoryInterface $eventRepository
    ) {}

    public function storeEvent(array $data): array
    {
        if ($existing = $this->eventRepository->findByIdempotencyKey($data['idempotency_key'])) {
            return [
                'duplicate' => true,
                'event' => $existing
            ];
        }

        $event = $this->eventRepository->create($data);

        return [
            'duplicate' => false,
            'event' => $event
        ];
    }

    public function todayStats(string $today): array
    {
        $counts = $this->eventRepository->getTodayStats($today);

        return [
            'date' => $today,
            'counts' => [
                'page_view' => $counts['page_view'] ?? 0,
                'cta_click' => $counts['cta_click'] ?? 0,
                'form_submit' => $counts['form_submit'] ?? 0,
            ],
            'total' => array_sum($counts),
        ];
    }
}
