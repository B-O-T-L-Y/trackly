<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'ts' => $this->ts?->toIso8601String(),
            'session_id' => $this->session_id,
            'idempotency_key' => $this->idempotency_key,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
