# Teardrop Docker Management Makefile
.PHONY: help build up down restart logs shell artisan migrate seed fresh install clean backup restore test

# Default target
help:
	@echo "Teardrop Docker Management Commands"
	@echo "===================================="
	@echo ""
	@echo "Setup:"
	@echo "  make install      - Initial setup (build, up, migrate, seed)"
	@echo "  make build        - Build Docker images"
	@echo "  make up           - Start all services"
	@echo "  make down         - Stop all services"
	@echo "  make restart      - Restart all services"
	@echo ""
	@echo "Development:"
	@echo "  make logs         - View all logs (follow)"
	@echo "  make shell        - Enter app container shell"
	@echo "  make artisan      - Run artisan command (e.g., make artisan ARGS='route:list')"
	@echo "  make tinker       - Open Laravel Tinker"
	@echo "  make test         - Run PHPUnit tests"
	@echo ""
	@echo "Database:"
	@echo "  make migrate      - Run migrations"
	@echo "  make seed         - Run seeders"
	@echo "  make fresh        - Fresh migrate with seed"
	@echo "  make backup       - Backup database"
	@echo "  make restore      - Restore database from backup"
	@echo ""
	@echo "Maintenance:"
	@echo "  make clean        - Clear all caches"
	@echo "  make optimize     - Optimize application"
	@echo "  make reset        - Reset everything (⚠️  destroys data)"
	@echo ""

# Build Docker images
build:
	docker-compose build --no-cache

# Start all services
up:
	docker-compose up -d
	@echo "Waiting for services to be ready..."
	@sleep 5
	@docker-compose ps

# Stop all services
down:
	docker-compose down

# Restart all services
restart:
	docker-compose restart
	@sleep 3
	@docker-compose ps

# View logs
logs:
	docker-compose logs -f

# View specific service logs
logs-app:
	docker-compose logs -f app

logs-mysql:
	docker-compose logs -f mysql

logs-redis:
	docker-compose logs -f redis

logs-bitcoin:
	docker-compose logs -f bitcoin

logs-monero:
	docker-compose logs -f monero-daemon monero-wallet-rpc

# Enter app container shell
shell:
	docker-compose exec app bash

# Enter MySQL shell
mysql:
	docker-compose exec mysql mysql -u teardrop -psecret teardrop

# Run artisan command
artisan:
	docker-compose exec app php artisan $(ARGS)

# Open Tinker
tinker:
	docker-compose exec app php artisan tinker

# Run migrations
migrate:
	docker-compose exec app php artisan migrate --force

# Run seeders
seed:
	docker-compose exec app php artisan db:seed --force

# Fresh migration with seed
fresh:
	docker-compose exec app php artisan migrate:fresh --seed --force

# Run tests
test:
	docker-compose exec app php artisan test

# Clear all caches
clean:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "All caches cleared!"

# Optimize application
optimize:
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	@echo "Application optimized!"

# Backup database
backup:
	@mkdir -p backups
	@echo "Creating database backup..."
	docker-compose exec -T mysql mysqldump -u teardrop -psecret teardrop > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "Backup completed: backups/backup_$$(date +%Y%m%d_%H%M%S).sql"

# Restore database from latest backup
restore:
	@if [ -z "$(FILE)" ]; then \
		echo "Usage: make restore FILE=backups/backup_YYYYMMDD_HHMMSS.sql"; \
		exit 1; \
	fi
	@echo "Restoring database from $(FILE)..."
	docker-compose exec -T mysql mysql -u teardrop -psecret teardrop < $(FILE)
	@echo "Database restored!"

# Initial installation
install: build up
	@echo "Waiting for services to be ready..."
	@sleep 10
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --force
	docker-compose exec app php artisan storage:link
	@echo ""
	@echo "Installation complete!"
	@echo "Access the application at: http://localhost:8000"

# Reset everything (⚠️  destroys data)
reset:
	@echo "⚠️  WARNING: This will destroy all data!"
	@echo "Press Ctrl+C to cancel, or Enter to continue..."
	@read confirm
	docker-compose down -v
	docker-compose up -d
	@sleep 10
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate:fresh --seed --force
	docker-compose exec app php artisan storage:link
	@echo "System reset complete!"

# Check service status
status:
	@docker-compose ps
	@echo ""
	@echo "Service Health:"
	@echo "==============="
	@docker-compose exec app curl -sf http://localhost/ > /dev/null && echo "✓ App is healthy" || echo "✗ App is unhealthy"
	@docker-compose exec mysql mysqladmin ping -h localhost --silent && echo "✓ MySQL is healthy" || echo "✗ MySQL is unhealthy"
	@docker-compose exec redis redis-cli ping > /dev/null && echo "✓ Redis is healthy" || echo "✗ Redis is unhealthy"

# Monitor resource usage
stats:
	docker stats $$(docker-compose ps -q)
