# Trackly - Event Tracking Demo

Trackly is a minimal full-stack event tracking system built with:

- **Laravel 11** (backend API)
- **Nuxt 4** (frontend SPA)
- **Docker** (nginx + PHP-FPM + Node)
- **SQLite** (local dev database)
- **TailwindCSS** (frontend UI)

It demonstrates:

- Sending tracking events (page view, CTA click, form submit)
- Idempotent event creation via a unique per-request key
- Daily aggregated statistics
- Auto-generated tracking token used for authenticated API access
- Clean separation of layers (controllers, services, repositories)
- Fully dockerized environment with zero manual setup

## Documentation

Documentation is located in the `.docs` directory.

### Start here:

| Section                                       | Description                                   |
|-----------------------------------------------|-----------------------------------------------|
| **[Installation](./.docs/installation.md)**   | How to build & run the project locally        |
| **[Backend API](./.docs/backend.md)**         | Laravel 11 API specification                  |
| **[Frontend SPA](./.docs/frontend.md)**       | Nuxt 4 UI and API integration                 |
| **[Postman Collection](./.docs/postman.md)**  | Testing the API with automated token fetching |
| **[Architecture](./.docs/architecture.md)**   | How Docker + backend + frontend communicate   |

## Quick Start

Run the stack:

```shell
  make build
```

or if already built:

```shell
  make up
```

or if already built:

```shell
  make down
```

Open dev terminals:

```shell
  make back # backend
````

```shell
  make front # frontend
```

## Tech Overview

### Backend (Laravel 11)

- `EventController` / `StatsController` 
- `EventService` and repository abstraction 
- Request validation with idempotency 
- Middleware `TrackingTokenAuth` 
- Auto-generated `TRACKING_TOKEN` on every container start 
- SQLite as local dev storage

### Frontend (Nuxt 4)

- `useApi` plugin with automatic Bearer injection 
- `useTrackingToken` to fetch token from backend `/dev/token` 
- `useAnalytics` for sending events 
- Styled via `TailwindCSS` 
- Real-time stats polling every N seconds

### Docker

- `backend` – PHP 8.4 + Supervisor 
- `nginx` – serves Laravel public directory 
- `frontend` – Node 24 + Nuxt dev server

## Project Structure

```
.
├── backend/        # Laravel API
├── frontend/       # Nuxt 4 SPA
├── .docker/        # Dockerfiles + configs
├── .docs/          # Project documentation
├── .postman/       # API collection
├── docker-compose.yml
└── Makefile
```

## API Endpoints

Authenticated with `Bearer TRACKING_TOKEN`.

```http request
POST /api/v1/events
GET  /api/v1/stats/today
GET  /api/dev/token   (local only)
```

## Development Notes

- Backend regenerates `TRACKING_TOKEN` on each container start. 
- Frontend and Postman both auto-refresh the token. 
- No external DB needed - uses SQLite. 
- Clean dependency injection setup using interfaces.

## License

MIT (as in Laravel skeleton)

[Next ➡](.docs/backend.md)