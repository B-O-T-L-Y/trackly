build:
	docker compose up -d --build

up:
	docker compose up -d

down:
	docker compose down

back:
	docker compose exec backend sh

front:
	docker compose exec frontend sh