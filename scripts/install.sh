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
SOURCE_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"   # project root where install.sh lives
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
    rsync \
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
    redis-server \
    memcached \
    certbot python3-certbot-nginx \
    openssl \
    unzip \
    wget gnupg2 tar bzip2 \
    || die "Failed to install server packages"

log "Nginx, Tor, PostgreSQL, Redis, Memcached, Certbot and utilities installed"

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
step "5/11  Deploying project files to ${PROJECT_ROOT}"
# ==============================================================================
if [[ "${SOURCE_ROOT}" == "${PROJECT_ROOT}" ]]; then
    log "Source and target are the same directory — skipping copy"
else
    if [[ ! -d "${SOURCE_ROOT}/artisan" && ! -f "${SOURCE_ROOT}/artisan" ]]; then
        die "Source does not look like a Laravel project (no artisan found in ${SOURCE_ROOT})"
    fi

    mkdir -p "${PROJECT_ROOT}"

    # rsync the project, excluding runtime/dev artifacts that shouldn't be copied
    rsync -a --delete \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='.env' \
        --exclude='storage/logs/*.log' \
        --exclude='storage/framework/cache/data/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        "${SOURCE_ROOT}/" "${PROJECT_ROOT}/" \
        || die "Failed to copy project files from ${SOURCE_ROOT} to ${PROJECT_ROOT}"

    # Ensure writable storage subdirectories exist even if they were empty
    mkdir -p "${PROJECT_ROOT}/storage/"{logs,framework/{cache/data,sessions,views}}
    mkdir -p "${PROJECT_ROOT}/bootstrap/cache"

    chown -R "${APP_USER}:www-data" "${PROJECT_ROOT}"
    log "Project files deployed from ${SOURCE_ROOT} → ${PROJECT_ROOT}"
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
step "7/11  SSL certificate"
# ==============================================================================
SSL_CERT_DIR="/etc/ssl/${PROJECT_NAME}"
SSL_CERT="${SSL_CERT_DIR}/selfsigned.crt"
SSL_KEY="${SSL_CERT_DIR}/selfsigned.key"

if [[ "${DOMAIN}" == "localhost" || "${DOMAIN}" == "127.0.0.1" ]]; then
    warn "Domain is '${DOMAIN}' — Let's Encrypt cannot validate localhost"
    warn "Generating a self-signed SSL certificate instead"

    mkdir -p "${SSL_CERT_DIR}"
    if [[ ! -f "${SSL_CERT}" ]]; then
        openssl req -x509 -nodes -days 3650 \
            -newkey rsa:2048 \
            -keyout "${SSL_KEY}" \
            -out    "${SSL_CERT}" \
            -subj   "/C=US/ST=Local/L=Local/O=${PROJECT_NAME}/CN=${DOMAIN}" \
            || die "Failed to generate self-signed certificate"
        log "Self-signed certificate created at ${SSL_CERT_DIR}/"
    else
        log "Self-signed certificate already exists — skipping"
    fi
else
    # Real domain — attempt Let's Encrypt via Certbot
    if certbot certonly --nginx -d "${DOMAIN}" --non-interactive --agree-tos \
        --register-unsafely-without-email 2>/dev/null; then
        SSL_CERT="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
        SSL_KEY="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"
        log "Let's Encrypt certificate obtained for ${DOMAIN}"
    else
        warn "Certbot failed — falling back to self-signed certificate"
        mkdir -p "${SSL_CERT_DIR}"
        openssl req -x509 -nodes -days 3650 \
            -newkey rsa:2048 \
            -keyout "${SSL_KEY}" \
            -out    "${SSL_CERT}" \
            -subj   "/C=US/ST=Local/L=Local/O=${PROJECT_NAME}/CN=${DOMAIN}" \
            || die "Failed to generate fallback self-signed certificate"
        log "Fallback self-signed certificate created at ${SSL_CERT_DIR}/"
    fi
fi

log "SSL cert: ${SSL_CERT}"
log "SSL key:  ${SSL_KEY}"

# ==============================================================================
step "8/11  Configuring Nginx site: ${NGINX_SITE_NAME}"
# ==============================================================================
NGINX_AVAILABLE="/etc/nginx/sites-available/${NGINX_SITE_NAME}"
NGINX_ENABLED="/etc/nginx/sites-enabled/${NGINX_SITE_NAME}"

if [[ ! -f "${NGINX_TEMPLATE}" ]]; then
    die "Nginx config template not found at ${NGINX_TEMPLATE}"
fi

# Deploy the site config (both HTTP + HTTPS server blocks in one file)
cp "${NGINX_TEMPLATE}" "${NGINX_AVAILABLE}"
sed -i \
    -e "s|__PROJECT_ROOT__|${PROJECT_ROOT}|g" \
    -e "s|__PROJECT_NAME__|${PROJECT_NAME}|g" \
    -e "s|__DOMAIN__|${DOMAIN}|g" \
    -e "s|__SSL_CERT__|${SSL_CERT}|g" \
    -e "s|__SSL_KEY__|${SSL_KEY}|g" \
    -e "s|__PHP_VERSION__|${PHP_VERSION}|g" \
    "${NGINX_AVAILABLE}"

log "Nginx config written to ${NGINX_AVAILABLE}"

# Remove stale SSL-only site from previous install runs (if any)
for stale in "/etc/nginx/sites-enabled/${NGINX_SITE_NAME}-ssl" \
             "/etc/nginx/sites-available/${NGINX_SITE_NAME}-ssl"; do
    [[ -e "${stale}" || -L "${stale}" ]] && rm -f "${stale}"
done

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

# Storage & cache: directories 775, files 664 (group-writable, no execute on files)
find "${PROJECT_ROOT}/storage" "${PROJECT_ROOT}/bootstrap/cache" -type d -exec chmod 775 {} \;
find "${PROJECT_ROOT}/storage" "${PROJECT_ROOT}/bootstrap/cache" -type f -exec chmod 664 {} \;

log "Permissions set on ${PROJECT_ROOT}"

# ==============================================================================
step "10/11  Enabling services"
# ==============================================================================
systemctl enable --now nginx        || warn "Could not enable nginx"
systemctl enable --now postgresql   || warn "Could not enable postgresql"
systemctl enable --now tor          || warn "Could not enable tor"
systemctl enable --now "php${PHP_VERSION}-fpm" || warn "Could not enable php-fpm"
systemctl enable --now redis-server || warn "Could not enable redis-server"
systemctl enable --now memcached    || warn "Could not enable memcached"

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

# Re-apply ownership on storage after composer/npm may have created files as APP_USER's default group
chown -R "${APP_USER}:www-data" "${PROJECT_ROOT}/storage" "${PROJECT_ROOT}/bootstrap/cache"
log "Storage ownership re-applied (${APP_USER}:www-data)"

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
