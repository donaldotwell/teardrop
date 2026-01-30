# Teardrop - Laravel 11 Escrow Marketplace
# Multi-stage Docker build for production deployment
# Base image: PHP 8.2 with Apache

FROM php:8.2-apache

LABEL maintainer="Teardrop Dev Team"
LABEL description="Teardrop Escrow Marketplace - Laravel 11 with Bitcoin/Monero support"

# Set working directory
WORKDIR /var/www/html

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    NODE_VERSION=20.x

# Install system dependencies
RUN apt-get update && apt-get install -y \
    # Base utilities
    wget \
    gnupg2 \
    tar \
    bzip2 \
    curl \
    git \
    unzip \
    vim \
    supervisor \
    cron \
    # Image processing (QR code generation)
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    # GD dependencies
    libgd-dev \
    # PGP/GPG support
    gnupg \
    libgpgme-dev \
    # Redis client
    redis-tools \
    # MySQL client
    default-mysql-client \
    # Zip/compression
    libzip-dev \
    zip \
    # XML/XSLT
    libxml2-dev \
    libxslt1-dev \
    # String manipulation
    libonig-dev \
    # Internationalization
    libicu-dev \
    # Process control
    libpcntl-dev \
    # Bcmath dependencies
    libbz2-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) \
        # Core Laravel requirements
        pdo \
        pdo_mysql \
        mysqli \
        # Image processing (QR codes)
        gd \
        # Encryption/security
        bcmath \
        # String manipulation
        mbstring \
        # XML processing
        xml \
        xsl \
        # Zip support
        zip \
        # Math operations
        exif \
        # Internationalization
        intl \
        # Process control (queue workers)
        pcntl \
        # POSIX functions
        posix \
        # Sockets (Redis, RPC)
        sockets \
        # Opcache for performance
        opcache

# Install PECL extensions
RUN pecl install redis-6.0.2 gnupg-1.5.1 \
    && docker-php-ext-enable redis gnupg

# Install Node.js (for Vite/Tailwind build)
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION} | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=10M'; \
        echo 'post_max_size=10M'; \
        echo 'max_execution_time=300'; \
        echo 'max_input_vars=5000'; \
        echo 'date.timezone=UTC'; \
        echo 'expose_php=Off'; \
        # Opcache settings
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
    } > "$PHP_INI_DIR/conf.d/custom.ini"

# Configure Apache
RUN a2enmod rewrite headers expires deflate ssl \
    && sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && { \
        echo '<Directory /var/www/html/public>'; \
        echo '    Options -Indexes +FollowSymLinks'; \
        echo '    AllowOverride All'; \
        echo '    Require all granted'; \
        echo '</Directory>'; \
    } >> /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# Install Node dependencies and build assets
RUN npm ci && npm run build && npm cache clean --force

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Create supervisor configuration for queue workers
RUN mkdir -p /var/log/supervisor \
    && { \
        echo '[supervisord]'; \
        echo 'nodaemon=true'; \
        echo 'user=root'; \
        echo ''; \
        echo '[program:laravel-worker]'; \
        echo 'process_name=%(program_name)s_%(process_num)02d'; \
        echo 'command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600'; \
        echo 'autostart=true'; \
        echo 'autorestart=true'; \
        echo 'stopasgroup=true'; \
        echo 'killasgroup=true'; \
        echo 'user=www-data'; \
        echo 'numprocs=2'; \
        echo 'redirect_stderr=true'; \
        echo 'stdout_logfile=/var/www/html/storage/logs/worker.log'; \
        echo 'stopwaitsecs=3600'; \
        echo ''; \
        echo '[program:laravel-scheduler]'; \
        echo 'command=/bin/bash -c "while [ true ]; do (php /var/www/html/artisan schedule:run --verbose --no-interaction &); sleep 60; done"'; \
        echo 'autostart=true'; \
        echo 'autorestart=true'; \
        echo 'user=www-data'; \
        echo 'redirect_stderr=true'; \
        echo 'stdout_logfile=/var/www/html/storage/logs/scheduler.log'; \
        echo ''; \
        echo '[program:apache2]'; \
        echo 'command=/usr/sbin/apache2ctl -D FOREGROUND'; \
        echo 'autostart=true'; \
        echo 'autorestart=true'; \
        echo 'stdout_logfile=/dev/stdout'; \
        echo 'stdout_logfile_maxbytes=0'; \
        echo 'stderr_logfile=/dev/stderr'; \
        echo 'stderr_logfile_maxbytes=0'; \
    } > /etc/supervisor/conf.d/supervisord.conf

# Create entrypoint script
RUN { \
        echo '#!/bin/bash'; \
        echo 'set -e'; \
        echo ''; \
        echo '# Wait for database to be ready'; \
        echo 'echo "Waiting for database connection..."'; \
        echo 'until php artisan db:show > /dev/null 2>&1; do'; \
        echo '    echo "Database is unavailable - sleeping"'; \
        echo '    sleep 2'; \
        echo 'done'; \
        echo ''; \
        echo 'echo "Database is up - continuing..."'; \
        echo ''; \
        echo '# Run migrations'; \
        echo 'php artisan migrate --force'; \
        echo ''; \
        echo '# Clear and cache config'; \
        echo 'php artisan config:cache'; \
        echo 'php artisan route:cache'; \
        echo 'php artisan view:cache'; \
        echo ''; \
        echo '# Create storage link'; \
        echo 'php artisan storage:link || true'; \
        echo ''; \
        echo '# Start supervisor'; \
        echo 'exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf'; \
    } > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
