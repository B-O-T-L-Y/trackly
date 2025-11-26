<?php

namespace App\Http\Resources\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource['date'],
            'counts' => [
                'page_view' => $this->resource['counts']['page_view'] ?? 0,
                'cta_click' => $this->resource['counts']['cta_click'] ?? 0,
                'form_submit' => $this->resource['counts']['form_submit'] ?? 0,
            ],
            'total' => $this->resource['total'],
        ];
    }
}
