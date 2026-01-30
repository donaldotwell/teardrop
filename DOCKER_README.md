# Teardrop Docker Deployment Guide

Complete Docker setup for the Teardrop Laravel 11 Escrow Marketplace with Bitcoin and Monero support.

## 🐳 Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 4GB RAM
- 50GB disk space (for blockchain data)

## 📦 What's Included

The Docker setup includes:

### Application Stack
- **Laravel 11** (PHP 8.2-Apache)
- **MySQL 8.0** - Database
- **Redis 7** - Cache & Queue
- **Supervisor** - Process management (queue workers, scheduler)

### Cryptocurrency Services
- **Bitcoin Core** - Testnet with RPC
- **Monero Daemon** - Testnet (offline mode for dev)
- **Monero Wallet RPC** - Wallet operations

### PHP Extensions Installed
- **Core**: pdo, pdo_mysql, mysqli, bcmath, mbstring
- **Image**: gd (for QR code generation with freetype, jpeg, webp, xpm)
- **Security**: gnupg (for PGP encryption)
- **Cache**: redis
- **Process**: pcntl, posix, sockets
- **Performance**: opcache
- **Other**: xml, xsl, zip, exif, intl

## 🚀 Quick Start

### 1. Clone and Configure

```bash
cd /path/to/teardrop
cp .env.example .env
```

### 2. Update .env for Docker

Edit `.env` with Docker service hostnames:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=teardrop
DB_USERNAME=teardrop
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379

# Bitcoin RPC
BITCOINRPC_SCHEME=http
BITCOINRPC_HOST=bitcoin
BITCOINRPC_PORT=18332
BITCOINRPC_USER=bitcoin
BITCOINRPC_PASSWORD=bitcoinrpc123

# Monero Wallet RPC
MONERO_RPC_SCHEME=http
MONERO_RPC_HOST=monero-wallet-rpc
MONERO_RPC_PORT=28084
MONERO_RPC_USER=
MONERO_RPC_PASSWORD=
MONERO_WALLET_DIR=/home/monero/wallets
MONERO_NETWORK=testnet
```

### 3. Build and Start

```bash
# Build the image
docker-compose build

# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app
```

### 4. Initialize Application

```bash
# Generate app key (if not in .env)
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate --seed

# Create storage link
docker-compose exec app php artisan storage:link
```

### 5. Access Application

- **Web**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **Bitcoin RPC**: localhost:18332
- **Monero Daemon**: localhost:28081
- **Monero Wallet RPC**: localhost:28084

## 🔧 Management Commands

### Application

```bash
# Enter container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan [command]

# Run tinker
docker-compose exec app php artisan tinker

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# View queue jobs
docker-compose exec app php artisan queue:work --once

# Check logs
docker-compose exec app tail -f storage/logs/laravel.log
```

### Database

```bash
# MySQL console
docker-compose exec mysql mysql -u teardrop -psecret teardrop

# Backup database
docker-compose exec mysql mysqldump -u teardrop -psecret teardrop > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u teardrop -psecret teardrop < backup.sql
```

### Bitcoin

```bash
# Bitcoin CLI
docker-compose exec bitcoin bitcoin-cli -testnet [command]

# Get blockchain info
docker-compose exec bitcoin bitcoin-cli -testnet getblockchaininfo

# Generate blocks (testnet)
docker-compose exec bitcoin bitcoin-cli -testnet generatetoaddress 100 [address]

# Create wallet
docker-compose exec bitcoin bitcoin-cli -testnet createwallet "wallet_name"

# List wallets
docker-compose exec bitcoin bitcoin-cli -testnet listwallets
```

### Monero

```bash
# Check daemon status
curl http://localhost:28081/get_info | jq

# Check wallet-rpc status
curl -X POST http://localhost:28084/json_rpc \
  -d '{"jsonrpc":"2.0","id":"0","method":"get_version"}' \
  -H 'Content-Type: application/json' | jq

# Generate testnet blocks
curl -X POST http://localhost:28081/json_rpc \
  -d '{"jsonrpc":"2.0","id":"0","method":"generateblocks","params":{"amount_of_blocks":100,"wallet_address":"ADDRESS"}}' \
  -H 'Content-Type: application/json'
```

## 📊 Monitoring

### Health Checks

```bash
# Check all services status
docker-compose ps

# Check specific service health
docker-compose exec app curl -f http://localhost/ || echo "App unhealthy"
docker-compose exec mysql mysqladmin ping -h localhost || echo "MySQL unhealthy"
docker-compose exec redis redis-cli ping || echo "Redis unhealthy"
```

### Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
docker-compose logs -f redis
docker-compose logs -f bitcoin
docker-compose logs -f monero-daemon
docker-compose logs -f monero-wallet-rpc

# Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log

# Queue worker logs
docker-compose exec app tail -f storage/logs/worker.log

# Scheduler logs
docker-compose exec app tail -f storage/logs/scheduler.log
```

## 🔄 Updates

```bash
# Pull latest code
git pull

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force
```

## 🗑️ Cleanup

```bash
# Stop all services
docker-compose down

# Remove volumes (⚠️ deletes all data)
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Prune unused Docker resources
docker system prune -a --volumes
```

## 🐛 Troubleshooting

### Application won't start

```bash
# Check logs
docker-compose logs app

# Verify .env configuration
docker-compose exec app cat .env

# Check permissions
docker-compose exec app ls -la storage
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database connection failed

```bash
# Check MySQL is running
docker-compose ps mysql

# Verify credentials
docker-compose exec mysql mysql -u teardrop -psecret teardrop

# Check from app container
docker-compose exec app php artisan db:show
```

### Queue not processing

```bash
# Check supervisor status
docker-compose exec app supervisorctl status

# Restart workers
docker-compose restart app

# Manual queue work
docker-compose exec app php artisan queue:work --once
```

### Bitcoin/Monero RPC not responding

```bash
# Check service status
docker-compose ps bitcoin monero-daemon monero-wallet-rpc

# Restart services
docker-compose restart bitcoin monero-daemon monero-wallet-rpc

# Check connectivity from app
docker-compose exec app curl http://bitcoin:18332
docker-compose exec app curl http://monero-wallet-rpc:28084/json_rpc
```

## 🔒 Production Considerations

### Security

1. **Change default passwords** in `.env`:
   - DB_PASSWORD
   - REDIS_PASSWORD (if enabled)
   - BITCOINRPC_PASSWORD

2. **Use HTTPS**: Add nginx reverse proxy with SSL

3. **Restrict ports**: Only expose necessary ports

4. **Enable firewall**: UFW or iptables

### Performance

1. **Increase resources** in `docker-compose.yml`:
   ```yaml
   app:
     deploy:
       resources:
         limits:
           cpus: '2'
           memory: 2G
   ```

2. **Optimize MySQL**: Adjust `docker/mysql/my.cnf`

3. **Scale workers**:
   ```bash
   docker-compose up -d --scale app=3
   ```

### Backup Strategy

```bash
# Automated backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump -u teardrop -psecret teardrop > backup_$DATE.sql
tar -czf storage_$DATE.tar.gz storage/
```

## 📝 Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | mysql | Database hostname |
| `DB_PORT` | 3306 | Database port |
| `REDIS_HOST` | redis | Redis hostname |
| `BITCOINRPC_HOST` | bitcoin | Bitcoin RPC host |
| `MONERO_RPC_HOST` | monero-wallet-rpc | Monero wallet RPC host |

## 📚 Additional Resources

- [Laravel Deployment](https://laravel.com/docs/11.x/deployment)
- [Bitcoin Core RPC](https://developer.bitcoin.org/reference/rpc/)
- [Monero RPC Documentation](https://www.getmonero.org/resources/developer-guides/wallet-rpc.html)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)

## 🆘 Support

For issues specific to Docker deployment, check:
1. Service logs: `docker-compose logs [service]`
2. Container status: `docker-compose ps`
3. Resource usage: `docker stats`

For application issues, see the main README.md.
