[🏠 Main](../README.md)

`.docs/installation.md`

# Trackly - Installation and Local Environment

Trackly is a minimal event tracking demo built with Laravel 11 (API backend) and Nuxt 4 (SPA frontend), running in Docker.

This page explains how to start the project locally using Docker and Makefile helpers.

## 1. Prerequisites

- Docker and Docker Compose v2
- Make (optional, but recommended)
- Git

No local PHP, Node, or database installation is required - everything runs in containers.

## 2. Project structure (high level)

- `backend/` - Laravel 11 API
- `frontend/` - Nuxt 4 app
- `.docker/` - Dockerfiles and Nginx/PHP configuration
- `.postman/` - Postman collection for manual API testing
- `docker-compose.yml` - defines `nginx`, `backend`, and `frontend` services
- `Makefile` - shortcuts for common Docker commands

## 3. First run

Clone the repository, then from the project root run:

```shell
  make build
```

This command:

- builds all images (nginx, backend, frontend);
- starts containers in the background;
- runs `backend/entrypoint.sh` and `frontend/entrypoint.sh`.

On the first run the backend entrypoint will:

- install PHP dependencies via Composer;
- create `backend/.env` from `.env.example` if it does not exist;
- generate a fresh `APP_KEY` (if empty);
- generate a new `TRACKING_TOKEN` on every container start.

The frontend entrypoint will:

create `frontend/.env` from `.env.example` if it does not exist.

For subsequent runs you can use:

```shell
  make up # start existing containers
````  
```shell
  make down # stop and remove containers
```
## 4. URLs

After a successful start:

- Frontend: http://localhost:3000
- Backend API via Nginx: http://localhost:8000/api
- Development token endpoint (local only): http://localhost:8000/api/dev/token

The Nuxt app calls the backend through Nginx, not directly to the PHP container.

## 5. Environment configuration
### 5.1 Backend

Backend config lives in `backend/.env.` The example file:

```dotenv
APP_ENV=local
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
TRACKING_TOKEN=
TRACKING_TIMEZONE=UTC
```

On container start, `backend/entrypoint.sh`:

- copies `.env.example` to `.env` if needed;
- generates a new random `TRACKING_TOKEN` and writes it into `.env`:

```shell
  TRACKING_TOKEN_VALUE=$(php -r 'echo bin2hex(random_bytes(32));')
  sed -i "s/^TRACKING_TOKEN=.*/TRACKING_TOKEN=${TRACKING_TOKEN_VALUE}/" "$file"
```

### 5.2 Frontend

Frontend config uses Nuxt runtime config and `.env`:

`frontend/.env` (example):

```dotenv
NUXT_API_BASE_SERVER=http://nginx/api
NUXT_API_TOKEN_PATH=/dev/token
NUXT_PUBLIC_API_BASE_CLIENT=http://localhost:8000/api
NUXT_PUBLIC_STATS_POLL_INTERVAL=5
```

Key values:

- `NUXT_API_BASE_SERVER` - internal URL from the frontend container to the backend service.
- `NUXT_API_TOKEN_PATH` - path to the dev token endpoint.
- `NUXT_PUBLIC_API_BASE_CLIENT` - public API URL used in the browser.
- `NUXT_PUBLIC_STATS_POLL_INTERVAL` - polling interval (seconds) for stats refresh.

## 6. Useful Make targets

From the project root:

```
make build   # build images and start all containers
make up      # start containers (without rebuild)
make down    # stop and remove containers

make back    # open a shell inside the backend container
make front   # open a shell inside the frontend container
```

## 7. Migrating the database

The backend uses SQLite by default. To run migrations:

```shell
  make back
```

```
php artisan migrate
```

[⬅ Preview](../README.md) | [Next ➡](backend.md)