[🏠 Main](../README.md)

`.docs/backend.md`

# Backend API (Laravel 11)

The backend is a small `Laravel 11` application that exposes an authenticated API for recording events and querying daily statistics.


## 1. Overview

Key responsibilities:

- Accept event tracking POST requests.
- Enforce a simple Bearer token auth (`TRACKING_TOKEN`).
- Deduplicate events using an idempotency key.
- Aggregate daily statistics for page views, CTA clicks, and form submits.

Main entry points:

- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/EventController.php`
- `backend/app/Http/Controllers/Api/V1/StatsController.php`

## 2. Authentication: TrackingTokenAuth middleware

`backend/app/Http/Middleware/TrackingTokenAuth.php`:

- Reads the `Authorization` header.
- Ensures it starts with `Bearer `.
- Compares the token with `config('tracking.token')`, which is read from `TRACKING_TOKEN` in `.env`.

If the header is missing or the token does not match, the middleware returns:

```json
{
  "message": "Unauthorized"
}
```

with status `401`.

The middleware is registered in `backend/bootstrap/app.php`:

```php
$middleware->alias([
    'tracking.auth' => TrackingTokenAuth::class,
]);
```

and applied to all API v1 routes:

```php
Route::middleware(['tracking.auth'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/events', [EventController::class, 'store']);
        Route::prefix('stats')->group(function () {
            Route::get('/today', [StatsController::class, 'today']);
        });
    });
```

## 3. Configuration:

`config/tracking.php`

```php
return [
    'token' => env('TRACKING_TOKEN', 'secret'),
    'timezone' => env('TRACKING_TIMEZONE', 'UTC'),
];
```

- `TRACKING_TOKEN` - shared secret for API access (generated automatically by `entrypoint.sh`).
- `TRACKING_TIMEZONE` - timezone used when calculating “today” for stats.

## 4. Data model and migration
### 4.1 Migration

`backend/database/migrations/2025_11_25_104241_create_events_table.php` defines a simple `events` table:

- `id` - primary key
- `type` - event type (`page_view`, `cta_click`, `form_submit`)
- `ts` - timestamp of the event
- `session_id` - client session identifier
- `idempotency_key` - unique string used to deduplicate events
- standard `created_at` and `updated_at` timestamps

### 4.2 Model

`backend/app/Models/Event.php`:

```php
class Event extends Model
{
    protected $fillable = [
        'type',
        'ts',
        'session_id',
        'idempotency_key'
    ];

    protected $casts = [
        'ts' => 'datetime',
    ];
}
```

## 5. Repository and Service layers
### 5.1 Repository

`backend/app/Repositories/Event/EventRepository.php` implements `EventRepositoryInterface`:

- `create(array $data)` - creates a new `Event`.
- `findByIdempotencyKey(string $key)` - locates an event by `idempotency_key`.
- `getTodayStats(string $today)` - returns counts grouped by `type` for the given date.

The interface is bound in `AppServiceProvider`:

```php
$this->app->bind(
    EventRepositoryInterface::class,
    EventRepository::class
);
```

### 5.2 Service

`backend/app/Services/Event/EventService.php`:

- `storeEvent(array $data): array`
  - Checks if an event with the same `idempotency_key` already exists.
  - If yes, returns `['duplicate' => true, 'event' => $existing]`. 
  - Otherwise, creates a new event and returns `['duplicate' => false, 'event' => $event]`.
- `todayStats(string $today): array`
  - Uses the repository to fetch grouped counts for the date. 
  - Normalises them into a structure with `page_view`, `cta_click`, `form_submit`, and total.

## 6. HTTP layer
### 6.1 Request validation

`backend/app/Http/Requests/Event/StoreEventRequest.php`:

- Reads `X-Idempotency-Key` from request headers and merges it into the input as `idempotency_key`:

```php
protected function prepareForValidation(): void
{
    $this->merge([
        'idempotency_key' => $this->header('X-Idempotency-Key'),
    ]);
}
```

- Validates:
  - `type` - required, one of `page_view`, `cta_click`, `form_submit` 
  - `ts` - required, valid date 
  - `session_id` - required string 
  - `idempotency_key` - required UUID string

Custom error messages are provided for invalid `type` and missing/invalid idempotency key.

### 6.2 Controllers

**EventController**

`store()`:

1. Validates the request with StoreEventRequest. 
2. Delegates to EventService::storeEvent. 
3. Returns an EventResource with extra metadata:

If duplicate:

```json
{
  "data": { ...event },
  "duplicate": true
}
```
If new event:

```json
{
  "data": { ...event },
  "status": "created"
}
```

with HTTP status `201`.

**StatsController**

`today()`:

1. Computes “today” using `Carbon` and `config('tracking.timezone')`. 
2. Calls `EventService::todayStats`. 
3. Wraps the result in `EventStatsResource`.

## 7. Resources
### 7.1 EventResource

`backend/app/Http/Resources/Event/EventResource.php`:

- Serialises event fields:

```json
{
  "id": 1,
  "type": "page_view",
  "ts": "2025-11-28T10:00:00Z",
  "session_id": "session-uuid",
  "idempotency_key": "request-uuid",
  "created_at": "2025-11-28T10:00:01Z"
}
```

### 7.2 EventStatsResource

`backend/app/Http/Resources/Event/EventStatsResource.php`:

```json
{
  "date": "2025-11-28",
  "counts": {
    "page_view": 21,
    "cta_click": 23,
    "form_submit": 35
  },
  "total": 79
}
```

## 8. Development token endpoint

In `backend/routes/api.php`, for `local` environment only:

```php
Route::prefix('dev')->group(function () {
    Route::get('/token', function () {
        return response()->json([
            'token' => config('tracking.token'),
        ]);
    });
});
```

The frontend and the Postman collection use this endpoint to fetch the current `TRACKING_TOKEN` instead of hardcoding it.

[⬅ Preview](installation.md) | [Next ➡](frontend.md)