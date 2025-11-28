[🏠 Main](../README.md)

`.docs/architecture.md`

# Architecture Overview

This page describes how Trackly is structured across Docker services, backend, and frontend, and how a single tracking request flows through the system.

## 1. Docker services

`docker-compose.yml` defines three main services:

1. **backend** - PHP-FPM container running Laravel 11.
    - Based on `php:8.4-fpm-alpine`.
    - Uses `backend/entrypoint.sh` to install dependencies and prepare `.env`.
    - Runs under Supervisor (`.docker/php/supervisord.conf`).

2. **nginx** - front web server.
    - Based on `nginx:1.26-alpine`.
    - Uses `.docker/nginx/default.conf`.
    - Proxies PHP requests to `backend:9000`.
    - Serves the Laravel `public/` directory.

3. **frontend** - Nuxt 4 dev server.
    - Based on `node:24.11-alpine`.
    - Uses `frontend/entrypoint.sh` to ensure `.env` exists.
    - Runs `yarn dev --open` on port `3000`.

All containers share the same user-defined `api` network.

## 2. Nginx configuration

`./.docker/nginx/default.conf`:

- Root: `/var/www/html/public`
- PHP handling:

```nginx configuration
location ~ \.php$ {
    try_files $uri =404;
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass backend:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}
```

- Front controller:

```nginx configuration
location / {
    try_files $uri $uri/ /index.php?$query_string;
    gzip_static on;
}
```

This is a standard Laravel setup: all unknown paths are dispatched to `public/index.php`.

## 3. Entrypoints
### 3.1 Backend entrypoint

`backend/entrypoint.sh`:
1. Starts Supervisor. 
2. Runs `composer install`. 
3. Ensures `.env` exists (copies from `.env.example` if needed). 
4. Generates a new `TRACKING_TOKEN` on each start and writes it to `.env`. 
5. Reads `APP_KEY`; if empty, runs `php artisan key:generate`. 
6. Runs `php artisan optimize`.

This keeps the backend ready without manual `.env` handling.

## 3.2 Frontend entrypoint

`frontend/entrypoint.sh`:

1. Ensures `.env` exists (copies from `.env.example` if needed).

Executes the given command (by default `yarn dev --open`).

## 4. Request flow
### 4.1 From browser to backend

1. User opens `http://localhost:3000` (Nuxt dev server). 
2. When the page loads, the frontend:
   - Calls internal `/api/tracking-token` to fetch the current `TRACKING_TOKEN` from the backend. 
   - Calls `/v1/stats/today` via `$api` to get initial stats.

3. When the user clicks “Page View”, “CTA Click”, or “Form Submit”:
   - `useAnalytics.sendEvent(type)` is called. 
   - It generates:
     - `session_id` from local storage; 
     - `idempotency_key` header. 
   - Sends `POST /api/v1/events` via `$api`.

### 4.2 Inside Nuxt server / plugin

- `$api` is a `$fetch` client with:
  - base URL pointing to Nginx (`http://localhost:8000/api` in the browser, `http://nginx/api` on the server); 
  - `Authorization: Bearer <token>` header obtained via `useTrackingToken`.

### 4.3 Inside Laravel

1. Nginx forwards `/api/v1/events` to PHP-FPM. 
2. Laravel handles the request through the `api` route group. 
3. `TrackingTokenAuth` checks the Bearer token against `config('tracking.token')`. 
4. `StoreEventRequest` validates the payload and merges `X-Idempotency-Key` into the data. 
5. `EventController@store` delegates to `EventService`:
   - If an event with the same `idempotency_key` exists, it returns it with `duplicate: true`. 
   - Otherwise, it creates a new event and returns it with `status: "created"`. 
6. A JSON response is returned to Nuxt, which then shows a success toast.

### 4.4 Stats flow

- Nuxt periodically calls `GET /api/v1/stats/today` via `$api`. 
- `StatsController@today` calculates current date in configured timezone. 
- `EventService::todayStats` aggregates `events` table by type. 
- The resulting counters are wrapped in `EventStatsResource` and returned to the frontend.

## 5. Idempotency and session model

Trackly is intentionally simple but demonstrates two important concepts:

1. **Idempotency** - clients must send a unique `X-Idempotency-Key` per logical event. If the same key is reused, the backend treats the request as a duplicate and does not create a new row. 
2. Client sessions - `session_id` is computed in the browser and stored in `localStorage`. This makes it easy to later extend the system with per-session analytics.

## 6. Extending the system

Possible extension points:

- Add more event types (update validation rules and enums). 
- Add date range stats endpoints (not only “today”). 
- Persist frontend errors or latency metrics as separate events. 
- Integrate real-time updates (e.g. broadcasting stats via WebSockets).

[⬅ Preview](postman.md)