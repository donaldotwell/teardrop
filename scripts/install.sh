#!/usr/bin/env bash
# ==============================================================================
# Teardrop — Full Server Provisioning Script
# Must be executed as root or via sudo.
# ==============================================================================
set -euo pipefail

# ========================= Configuration ======================================
# Override any of these by exporting them before running the script, e.g.:
#   APP_USER=deploy DOMAIN=mysite.onion sudo -E ./install.sh
# ==============================================================================
APP_USER="${APP_USER:-forge}"
PROJECT_NAME="${PROJECT_NAME:-teardrop}"
PROJECT_ROOT="/home/${APP_USER}/${PROJECT_NAME}"
DOMAIN="${DOMAIN:-localhost}"
PHP_VERSION="${PHP_VERSION:-8.2}"
NODE_VERSION="${NODE_VERSION:-20.20.0}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NGINX_TEMPLATE="${SCRIPT_DIR}/config/nginx.config"
NGINX_SITE_NAME="${PROJECT_NAME}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ========================= Helpers ============================================
log()  { echo -e "${GREEN}[✔]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
err()  { echo -e "${RED}[✘]${NC} $*"; }
die()  { err "$@"; exit 1; }

step() {
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  $*${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# ========================= Preflight ==========================================
if [[ $EUID -ne 0 ]]; then
    die "This script must be run as root (use: sudo -E ./install.sh)"
fi

step "1/11  Installing system-level library dependencies"
# These must be present BEFORE the PHP extensions that depend on them.
apt-get update -qq
apt-get install -y --no-install-recommends \
    libgpgme-dev \
    imagemagick libmagickwand-dev \
    libmemcached-dev \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    curl \
    lsb-release \
    || die "Failed to install system library dependencies"
log "System library dependencies installed"

# ==============================================================================
step "2/11  Adding PHP ${PHP_VERSION} PPA & installing PHP + extensions"
# ==============================================================================
add-apt-repository ppa:ondrej/php -y
apt-get update -qq

# NOTE: Many extensions (calendar, exif, ffi, fileinfo, ftp, gettext, iconv,
# pcntl, pdo, shmop, sockets, sodium, sysvmsg, sysvsem, sysvshm, tokenizer)
# are built into php-common and do NOT have separate packages.
# dom, simplexml, xmlreader, xmlwriter are provided by php-xml.
# mysqli and pdo-mysql are provided by php-mysql.
# pdo-pgsql is provided by php-pgsql.
apt-get install -y \
    php${PHP_VERSION} \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-bz2 \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-gnupg \
    php${PHP_VERSION}-igbinary \
    php${PHP_VERSION}-imagick \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-memcached \
    php${PHP_VERSION}-msgpack \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-readline \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-xsl \
    php${PHP_VERSION}-zip \
    || die "Failed to install PHP ${PHP_VERSION} packages"

log "PHP ${PHP_VERSION} and extensions installed"

# ==============================================================================
step "3/11  Installing Nginx, Tor, PostgreSQL, Certbot & utilities"
# ==============================================================================
apt-get install -y \
    nginx \
    tor torsocks \
    postgresql postgresql-contrib \
    certbot python3-certbot-nginx \
    wget gnupg2 tar bzip2 \
    || die "Failed to install server packages"

log "Nginx, Tor, PostgreSQL, Certbot and utilities installed"

# ==============================================================================
step "4/11  Creating application user: ${APP_USER}"
# ==============================================================================
if id "${APP_USER}" &>/dev/null; then
    warn "User '${APP_USER}' already exists — skipping creation"
else
    adduser --disabled-password --gecos "" "${APP_USER}" \
        || die "Failed to create user '${APP_USER}'"
    log "User '${APP_USER}' created"
fi

# Ensure the user is in www-data group so Nginx/PHP-FPM can read files
usermod -aG www-data "${APP_USER}" 2>/dev/null || true
log "User '${APP_USER}' added to www-data group"

# ==============================================================================
step "5/11  Verifying project directory"
# ==============================================================================
if [[ ! -d "${PROJECT_ROOT}" ]]; then
    warn "Project directory ${PROJECT_ROOT} does not exist — creating it"
    mkdir -p "${PROJECT_ROOT}/public"
    mkdir -p "${PROJECT_ROOT}/storage"
    mkdir -p "${PROJECT_ROOT}/bootstrap/cache"
    chown -R "${APP_USER}:www-data" "${PROJECT_ROOT}"
    log "Created ${PROJECT_ROOT} with skeleton directories"
else
    log "Project directory ${PROJECT_ROOT} exists"
fi

# ==============================================================================
step "6/11  Installing Composer, NVM & Node.js ${NODE_VERSION}"
# ==============================================================================

# --- Composer ---
if command -v composer &>/dev/null; then
    log "Composer already installed ($(composer --version 2>/dev/null | head -1))"
else
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')" \
        || die "Failed to fetch Composer installer checksum"
    php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');" \
        || die "Failed to download Composer installer"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"

    if [[ "${EXPECTED_CHECKSUM}" != "${ACTUAL_CHECKSUM}" ]]; then
        rm -f /tmp/composer-setup.php
        die "Composer installer checksum mismatch — possible supply-chain attack"
    fi

    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
        || die "Composer installation failed"
    rm -f /tmp/composer-setup.php
    log "Composer installed to /usr/local/bin/composer"
fi

# --- NVM + Node.js (installed under APP_USER's home) ---
NVM_DIR="/home/${APP_USER}/.nvm"

if [[ -d "${NVM_DIR}" ]]; then
    log "NVM already installed at ${NVM_DIR}"
else
    su - "${APP_USER}" -c '
        curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
    ' || die "Failed to install NVM for user ${APP_USER}"
    log "NVM installed for user ${APP_USER}"
fi

# Install requested Node version and set as default
su - "${APP_USER}" -c "
    export NVM_DIR=\"${NVM_DIR}\"
    [ -s \"\${NVM_DIR}/nvm.sh\" ] && . \"\${NVM_DIR}/nvm.sh\"
    nvm install ${NODE_VERSION}
    nvm alias default ${NODE_VERSION}
    nvm use default
" || die "Failed to install Node.js ${NODE_VERSION}"
log "Node.js ${NODE_VERSION} installed and set as default for ${APP_USER}"

# ==============================================================================
step "7/11  Configuring Nginx site: ${NGINX_SITE_NAME}"
# ==============================================================================
NGINX_AVAILABLE="/etc/nginx/sites-available/${NGINX_SITE_NAME}"
NGINX_ENABLED="/etc/nginx/sites-enabled/${NGINX_SITE_NAME}"

if [[ ! -f "${NGINX_TEMPLATE}" ]]; then
    die "Nginx config template not found at ${NGINX_TEMPLATE}"
fi

# Copy template and substitute placeholders
cp "${NGINX_TEMPLATE}" "${NGINX_AVAILABLE}"
sed -i \
    -e "s|__PROJECT_ROOT__|${PROJECT_ROOT}|g" \
    -e "s|__PROJECT_NAME__|${PROJECT_NAME}|g" \
    -e "s|__DOMAIN__|${DOMAIN}|g" \
    "${NGINX_AVAILABLE}"

log "Nginx config written to ${NGINX_AVAILABLE}"

# Enable the site (symlink into sites-enabled)
if [[ -L "${NGINX_ENABLED}" ]]; then
    rm "${NGINX_ENABLED}"
fi
ln -s "${NGINX_AVAILABLE}" "${NGINX_ENABLED}"
log "Site enabled: ${NGINX_SITE_NAME}"

# Remove default site if it exists (avoids port conflicts)
if [[ -L /etc/nginx/sites-enabled/default ]]; then
    rm /etc/nginx/sites-enabled/default
    warn "Removed default Nginx site to avoid conflicts"
fi

# Validate and reload Nginx
nginx -t || die "Nginx configuration test failed — check ${NGINX_AVAILABLE}"
systemctl enable nginx
systemctl reload nginx
log "Nginx reloaded successfully"

# ==============================================================================
step "8/11  SSL certificate (Certbot)"
# ==============================================================================
# Certbot cannot obtain a real Let's Encrypt certificate for "localhost"
# because ACME challenge validation requires a publicly reachable domain.
# When DOMAIN=localhost we generate a self-signed certificate as a fallback.

SSL_CERT_DIR="/etc/ssl/${PROJECT_NAME}"

if [[ "${DOMAIN}" == "localhost" || "${DOMAIN}" == "127.0.0.1" ]]; then
    warn "Domain is '${DOMAIN}' — Let's Encrypt cannot validate localhost"
    warn "Generating a self-signed SSL certificate instead"

    mkdir -p "${SSL_CERT_DIR}"
    if [[ ! -f "${SSL_CERT_DIR}/selfsigned.crt" ]]; then
        openssl req -x509 -nodes -days 3650 \
            -newkey rsa:2048 \
            -keyout "${SSL_CERT_DIR}/selfsigned.key" \
            -out    "${SSL_CERT_DIR}/selfsigned.crt" \
            -subj   "/C=US/ST=Local/L=Local/O=${PROJECT_NAME}/CN=${DOMAIN}" \
            || die "Failed to generate self-signed certificate"
        log "Self-signed certificate created at ${SSL_CERT_DIR}/"
    else
        log "Self-signed certificate already exists — skipping"
    fi

    # Append SSL directives to the Nginx site config if not already present
    if ! grep -q "ssl_certificate" "${NGINX_AVAILABLE}"; then
        # Insert an SSL server block that redirects / terminates TLS
        cat >> "${NGINX_AVAILABLE}" <<SSLBLOCK

# --- Auto-generated SSL block (self-signed for localhost) ---
server {
    listen 127.0.0.1:443 ssl;
    listen [::1]:443 ssl;
    server_name ${DOMAIN};

    ssl_certificate     ${SSL_CERT_DIR}/selfsigned.crt;
    ssl_certificate_key ${SSL_CERT_DIR}/selfsigned.key;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    root ${PROJECT_ROOT}/public;
    index index.php;

    include /etc/nginx/sites-available/${NGINX_SITE_NAME};
}
SSLBLOCK
        nginx -t && systemctl reload nginx
        log "SSL block appended to Nginx config and reloaded"
    fi
else
    # Real domain — attempt Let's Encrypt via Certbot
    certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos \
        --register-unsafely-without-email --redirect \
        || {
            warn "Certbot failed — falling back to self-signed certificate"
            mkdir -p "${SSL_CERT_DIR}"
            openssl req -x509 -nodes -days 3650 \
                -newkey rsa:2048 \
                -keyout "${SSL_CERT_DIR}/selfsigned.key" \
                -out    "${SSL_CERT_DIR}/selfsigned.crt" \
                -subj   "/C=US/ST=Local/L=Local/O=${PROJECT_NAME}/CN=${DOMAIN}" \
                || die "Failed to generate fallback self-signed certificate"
            log "Fallback self-signed certificate created at ${SSL_CERT_DIR}/"
        }
    log "SSL configured for ${DOMAIN}"
fi

# ==============================================================================
step "9/11  Setting file permissions"
# ==============================================================================
# Home directory: accessible but not world-readable
chmod 711 "/home/${APP_USER}"

# Ownership: user owns files, www-data group for Nginx/PHP-FPM
chown -R "${APP_USER}:www-data" "${PROJECT_ROOT}"

# Directories: rwx for owner+group, execute for others
find "${PROJECT_ROOT}" -type d -exec chmod 775 {} \;

# Files: rw for owner+group, read for others
find "${PROJECT_ROOT}" -type f -exec chmod 664 {} \;

# Storage & cache need full group write + execute recursively
chmod -R 775 "${PROJECT_ROOT}/storage" "${PROJECT_ROOT}/bootstrap/cache"

log "Permissions set on ${PROJECT_ROOT}"

# ==============================================================================
step "10/11  Enabling services"
# ==============================================================================
systemctl enable --now nginx        || warn "Could not enable nginx"
systemctl enable --now postgresql   || warn "Could not enable postgresql"
systemctl enable --now tor          || warn "Could not enable tor"
systemctl enable --now "php${PHP_VERSION}-fpm" || warn "Could not enable php-fpm"

log "All services enabled and started"

# ==============================================================================
step "11/11  Installing application dependencies & building assets"
# ==============================================================================

# Run composer install as APP_USER (not as root)
su - "${APP_USER}" -c "
    cd '${PROJECT_ROOT}' && \
    composer install --no-interaction --prefer-dist --optimize-autoloader
" || die "composer install failed"
log "Composer dependencies installed"

# Run npm install + build as APP_USER (via NVM)
su - "${APP_USER}" -c "
    export NVM_DIR=\"/home/${APP_USER}/.nvm\"
    [ -s \"\${NVM_DIR}/nvm.sh\" ] && . \"\${NVM_DIR}/nvm.sh\"
    cd '${PROJECT_ROOT}' && \
    npm install && \
    npm run build
" || die "npm install / npm run build failed"
log "Node dependencies installed and assets built"

# ==============================================================================
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  Provisioning complete!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  User ............ ${APP_USER}"
echo "  Project root .... ${PROJECT_ROOT}"
echo "  Domain .......... ${DOMAIN}"
echo "  Nginx config .... ${NGINX_AVAILABLE}"
echo "  PHP version ..... ${PHP_VERSION}"
echo "  Node version .... ${NODE_VERSION}"
echo ""
echo "  Next steps:"
echo "    1. cd ${PROJECT_ROOT}"
echo "    2. cp .env.example .env && php artisan key:generate"
echo "    3. php artisan migrate --seed"
echo "    4. php artisan storage:link"
echo ""
