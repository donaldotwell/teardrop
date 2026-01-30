# 🐳 Teardrop Docker Quick Reference

## 📋 System Requirements Checklist

### ✅ PHP Extensions Installed
- [x] **Core**: PDO, PDO MySQL, MySQLi
- [x] **Math**: BCMath (cryptocurrency calculations)
- [x] **Strings**: MBString
- [x] **Image Processing**: GD (QR codes - with freetype, jpeg, webp, xpm)
- [x] **Security**: GnuPG (PGP encryption)
- [x] **Cache**: Redis
- [x] **Process Control**: PCNTL, POSIX
- [x] **Network**: Sockets
- [x] **XML**: XML, XSL
- [x] **Compression**: Zip
- [x] **Internationalization**: Intl
- [x] **Images**: Exif
- [x] **Performance**: Opcache

### ✅ System Packages
- [x] wget, gnupg2, tar, bzip2 (required by user)
- [x] curl, git, unzip (development tools)
- [x] supervisor (process management)
- [x] cron (scheduled tasks)
- [x] redis-tools (Redis CLI)
- [x] mysql-client (database tools)

### ✅ Composer Dependencies
- [x] Laravel 11 Framework
- [x] Bitcoin RPC (denpa/laravel-bitcoinrpc)
- [x] QR Code Generation (endroid/qr-code, simplesoftwareio/simple-qrcode)
- [x] Redis (predis/predis)
- [x] All development dependencies

### ✅ Node.js & NPM
- [x] Node.js 20.x
- [x] NPM latest
- [x] Vite (for asset compilation)
- [x] Tailwind CSS
- [x] Concurrently (for dev command)

## 🚀 Quick Start Commands

```bash
# Initial setup (one-time)
make install

# Daily development
make up          # Start services
make logs        # Watch logs
make shell       # Enter container
make down        # Stop services

# Database operations
make migrate     # Run migrations
make seed        # Seed database
make fresh       # Fresh start

# Artisan commands
make artisan ARGS="route:list"
make artisan ARGS="queue:work"
make tinker      # Laravel Tinker

# Maintenance
make clean       # Clear caches
make optimize    # Cache configs
make backup      # Backup database
```

## 🔌 Service Ports

| Service | Port | Access |
|---------|------|--------|
| Laravel App | 8000 | http://localhost:8000 |
| MySQL | 3306 | localhost:3306 |
| Redis | 6379 | localhost:6379 |
| Bitcoin RPC | 18332 | localhost:18332 |
| Monero Daemon | 28081 | http://localhost:28081 |
| Monero Wallet RPC | 28084 | http://localhost:28084 |

## 🔐 Default Credentials

### MySQL
- **User**: teardrop
- **Password**: secret
- **Database**: teardrop

### Bitcoin RPC
- **User**: bitcoin
- **Password**: bitcoinrpc123

### Monero RPC
- **Auth**: Disabled (--disable-rpc-login)

⚠️ **Change these in production!**

## 📁 Docker Files Created

```
teardrop/
├── Dockerfile                 # Main application image
├── docker-compose.yml         # Multi-service orchestration
├── .dockerignore             # Build context exclusions
├── .env.docker               # Docker environment template
├── Makefile                  # Management commands
├── DOCKER_README.md          # Full documentation
└── docker/
    ├── bitcoin/
    │   └── bitcoin.conf      # Bitcoin Core config
    └── mysql/
        └── my.cnf            # MySQL optimization
```

## 🏗️ Build Process

The Dockerfile:
1. ✅ Uses PHP 8.2 with Apache (production-ready)
2. ✅ Installs all system dependencies
3. ✅ Compiles PHP extensions with proper flags
4. ✅ Installs PECL extensions (redis, gnupg)
5. ✅ Installs Node.js 20.x and Composer 2.x
6. ✅ Configures PHP for production (opcache, security)
7. ✅ Sets up Apache with mod_rewrite
8. ✅ Installs Composer dependencies (--no-dev)
9. ✅ Builds frontend assets (npm run build)
10. ✅ Sets proper permissions for Laravel
11. ✅ Configures Supervisor for queue workers
12. ✅ Creates entrypoint for database migrations
13. ✅ Adds health checks

## 🔍 Verification Commands

```bash
# Check PHP version and extensions
docker-compose exec app php -v
docker-compose exec app php -m | grep -E 'gd|redis|gnupg|bcmath'

# Verify GD image support
docker-compose exec app php -r "var_dump(gd_info());"

# Check GnuPG
docker-compose exec app php -r "echo extension_loaded('gnupg') ? 'GnuPG OK' : 'Missing';"

# Verify Node.js
docker-compose exec app node --version
docker-compose exec app npm --version

# Check Composer packages
docker-compose exec app composer show | grep -E 'qr-code|bitcoin|redis'

# Test application
docker-compose exec app php artisan --version
docker-compose exec app php artisan route:list | head -n 20
```

## 🐛 Common Issues & Fixes

### Permission Errors
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Can't Generate QR Codes
```bash
# Verify GD extension
docker-compose exec app php -r "var_dump(gd_info());"
# Should show: GD Support => enabled, FreeType Support => enabled
```

### PGP Operations Fail
```bash
# Check gnupg extension
docker-compose exec app php -r "phpinfo();" | grep -i gnupg
```

### Queue Not Processing
```bash
# Check supervisor status
docker-compose exec app supervisorctl status
# Restart workers
docker-compose exec app supervisorctl restart laravel-worker:*
```

### Out of Memory
```bash
# Increase memory in docker-compose.yml
app:
  deploy:
    resources:
      limits:
        memory: 2G
```

## 📊 Monitoring

```bash
# Service status
make status

# Resource usage
make stats

# Live logs
make logs

# Specific service logs
make logs-app
make logs-mysql
make logs-bitcoin
```

## 🔄 Update Workflow

```bash
# 1. Pull latest code
git pull

# 2. Stop services
make down

# 3. Rebuild
make build

# 4. Start services
make up

# 5. Run migrations
make migrate

# 6. Clear caches
make clean && make optimize
```

## 🎯 Production Deployment

1. **Update .env** - Use `.env.docker` as template
2. **Change passwords** - All default credentials
3. **Enable HTTPS** - Add nginx reverse proxy
4. **Set APP_DEBUG=false**
5. **Configure mail** - SMTP settings
6. **Setup backups** - Automated database dumps
7. **Monitor logs** - ELK stack or similar
8. **Scale workers** - Increase queue processes

## 📚 More Information

- Full documentation: [DOCKER_README.md](DOCKER_README.md)
- Laravel deployment: https://laravel.com/docs/11.x/deployment
- Docker best practices: https://docs.docker.com/develop/dev-best-practices/

---

**Built with ❤️ for Laravel 11 + Bitcoin + Monero**
