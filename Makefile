.PHONY: help build up down restart logs shell db-shell migrate fresh seed screenshots

# Default target
help:
	@echo "Kiosk Dashboard - Docker Commands"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@echo "  build      Build Docker images"
	@echo "  up         Start containers in detached mode"
	@echo "  down       Stop and remove containers"
	@echo "  restart    Restart containers"
	@echo "  logs       Show container logs"
	@echo "  shell      Open shell in app container"
	@echo "  db-shell   Open MySQL shell in db container"
	@echo "  migrate    Run database migrations"
	@echo "  fresh      Fresh migrate with seed"
	@echo "  sync       Run weather and prayer sync"
	@echo "  screenshots Capture demo screenshots and regenerate docs/screenshots.md (app must be running)"

# Build Docker images
build:
	docker-compose build

# Start containers
up:
	docker-compose up -d

# Stop containers
down:
	docker-compose down

# Restart containers
restart:
	docker-compose restart

# Show logs
logs:
	docker-compose logs -f

# Open shell in app container
shell:
	docker-compose exec app sh

# Open MySQL shell
db-shell:
	docker-compose exec db mysql -u kiosk -psecret kiosk_dashboard

# Run migrations
migrate:
	docker-compose exec app php artisan migrate

# Fresh migration with seed
fresh:
	docker-compose exec app php artisan migrate:fresh

# Run sync commands
sync:
	docker-compose exec app php artisan sync:weather
	docker-compose exec app php artisan sync:prayer

# Generate app key
key:
	docker-compose exec app php artisan key:generate --show

# Clear all caches
clear:
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan route:clear

# Capture demo screenshots and regenerate docs/screenshots.md (app must be running, e.g. composer run dev)
screenshots:
	npm run screenshots
