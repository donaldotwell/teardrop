#!/usr/bin/env bash
# ==============================================================================
# Teardrop — Tor Hidden Service Configuration
# Reads vanity .onion address keys from a source directory and configures
# Tor hidden services with proper permissions and torrc entries.
#
# Must be executed as root or via sudo.
#
# Usage:
#   sudo ./configure-tor.sh
#   ADDRESSES_DIR=/path/to/keys sudo -E ./configure-tor.sh
# ==============================================================================
set -euo pipefail

# ========================= Configuration ======================================
ADDRESSES_DIR="${ADDRESSES_DIR:-/root/addresses}"
TOR_DATA_DIR="${TOR_DATA_DIR:-/var/lib/tor}"
TOR_USER="${TOR_USER:-debian-tor}"
TOR_GROUP="${TOR_GROUP:-debian-tor}"
TORRC="${TORRC:-/etc/tor/torrc}"
TORRC_BACKUP="${TORRC}.bak.$(date +%Y%m%d%H%M%S)"
LISTEN_ADDR="${LISTEN_ADDR:-127.0.0.1}"
LISTEN_PORT="${LISTEN_PORT:-80}"
HIDDEN_SERVICE_VERSION=3

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# ========================= Helpers ============================================
log()  { echo -e "${GREEN}[✔]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
err()  { echo -e "${RED}[✘]${NC} $*"; }
info() { echo -e "${CYAN}[i]${NC} $*"; }
die()  { err "$@"; exit 1; }

step() {
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  $*${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# ========================= Preflight ==========================================
if [[ $EUID -ne 0 ]]; then
    die "This script must be run as root (use: sudo -E ./configure-tor.sh)"
fi

if ! command -v tor &>/dev/null; then
    die "Tor is not installed. Install it first: sudo apt install -y tor"
fi

if [[ ! -d "${ADDRESSES_DIR}" ]]; then
    die "Addresses directory not found: ${ADDRESSES_DIR}"
fi

# Collect valid address directories
ONION_DIRS=()
ERRORS=0

for dir in "${ADDRESSES_DIR}"/*/; do
    [[ -d "${dir}" ]] || continue
    name="$(basename "${dir}")"

    # Validate it looks like a v3 onion address (56 chars + .onion)
    if [[ ! "${name}" =~ ^[a-z2-7]{56}\.onion$ ]]; then
        warn "Skipping '${name}' — does not match v3 .onion address format"
        continue
    fi

    # Verify required key files exist
    missing=()
    for required_file in hostname hs_ed25519_public_key hs_ed25519_secret_key; do
        if [[ ! -f "${dir}${required_file}" ]]; then
            missing+=("${required_file}")
        fi
    done

    if [[ ${#missing[@]} -gt 0 ]]; then
        warn "Skipping '${name}' — missing files: ${missing[*]}"
        ((ERRORS++))
        continue
    fi

    # Verify hostname file content matches directory name
    hostname_content="$(cat "${dir}hostname" | tr -d '[:space:]')"
    if [[ "${hostname_content}" != "${name}" ]]; then
        warn "Skipping '${name}' — hostname file contains '${hostname_content}' (mismatch)"
        ((ERRORS++))
        continue
    fi

    ONION_DIRS+=("${dir}")
done

if [[ ${#ONION_DIRS[@]} -eq 0 ]]; then
    die "No valid .onion address directories found in ${ADDRESSES_DIR}"
fi

info "Found ${#ONION_DIRS[@]} valid onion address(es) (${ERRORS} skipped)"
echo ""
for dir in "${ONION_DIRS[@]}"; do
    info "  $(basename "${dir}")"
done

# ==============================================================================
step "1/5  Backing up current torrc"
# ==============================================================================
if [[ -f "${TORRC}" ]]; then
    cp "${TORRC}" "${TORRC_BACKUP}"
    log "Backed up ${TORRC} → ${TORRC_BACKUP}"
else
    warn "No existing torrc found at ${TORRC} — will create a new one"
fi

# ==============================================================================
step "2/5  Writing Tor configuration"
# ==============================================================================

# Strip any existing HiddenService* directives and our managed block
if [[ -f "${TORRC}" ]]; then
    # Remove our managed block if it exists from a previous run
    sed -i '/^## --- TEARDROP HIDDEN SERVICES START ---$/,/^## --- TEARDROP HIDDEN SERVICES END ---$/d' "${TORRC}"

    # Also remove any stray HiddenService lines outside our block (safety)
    sed -i '/^HiddenServiceDir /d; /^HiddenServicePort /d; /^HiddenServiceVersion /d' "${TORRC}"
fi

# Ensure base Tor settings are present
ensure_torrc_setting() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}" "${TORRC}" 2>/dev/null; then
        sed -i "s|^${key}.*|${key} ${value}|" "${TORRC}"
    else
        echo "${key} ${value}" >> "${TORRC}"
    fi
}

# Create torrc if it doesn't exist
touch "${TORRC}"

ensure_torrc_setting "SocksPort" "9050"
ensure_torrc_setting "ControlPort" "9051"
ensure_torrc_setting "CookieAuthentication" "1"

log "Base Tor settings configured (SocksPort, ControlPort, CookieAuthentication)"

# Append hidden service blocks
{
    echo ""
    echo "## --- TEARDROP HIDDEN SERVICES START ---"
    echo "## Managed by configure-tor.sh — do not edit manually"
    echo ""

    INDEX=0
    for dir in "${ONION_DIRS[@]}"; do
        name="$(basename "${dir}")"
        # Create a safe directory label: primary for first, mirror_N for the rest
        if [[ ${INDEX} -eq 0 ]]; then
            label="primary"
        else
            label="mirror_${INDEX}"
        fi

        service_dir="${TOR_DATA_DIR}/hidden_service_${label}"

        echo "# ${label}: ${name}"
        echo "HiddenServiceDir ${service_dir}/"
        echo "HiddenServicePort ${LISTEN_PORT} ${LISTEN_ADDR}:${LISTEN_PORT}"
        echo "HiddenServiceVersion ${HIDDEN_SERVICE_VERSION}"
        echo ""

        ((INDEX++))
    done

    echo "## --- TEARDROP HIDDEN SERVICES END ---"
} >> "${TORRC}"

log "Added ${#ONION_DIRS[@]} hidden service block(s) to ${TORRC}"

# ==============================================================================
step "3/5  Creating hidden service directories & copying keys"
# ==============================================================================

INDEX=0
for dir in "${ONION_DIRS[@]}"; do
    name="$(basename "${dir}")"

    if [[ ${INDEX} -eq 0 ]]; then
        label="primary"
    else
        label="mirror_${INDEX}"
    fi

    service_dir="${TOR_DATA_DIR}/hidden_service_${label}"

    # Create directory
    mkdir -p "${service_dir}"

    # Copy key files
    cp "${dir}hostname"                "${service_dir}/hostname"
    cp "${dir}hs_ed25519_public_key"   "${service_dir}/hs_ed25519_public_key"
    cp "${dir}hs_ed25519_secret_key"   "${service_dir}/hs_ed25519_secret_key"

    # Set strict permissions (Tor requires 700 on dir, 600 on keys)
    chown -R "${TOR_USER}:${TOR_GROUP}" "${service_dir}"
    chmod 700 "${service_dir}"
    chmod 600 "${service_dir}/hs_ed25519_secret_key"
    chmod 600 "${service_dir}/hs_ed25519_public_key"
    chmod 644 "${service_dir}/hostname"

    log "[${label}] Keys deployed to ${service_dir}/"

    ((INDEX++))
done

# Ensure parent tor directory ownership is correct
chown "${TOR_USER}:${TOR_GROUP}" "${TOR_DATA_DIR}"
chmod 700 "${TOR_DATA_DIR}"

log "All hidden service directories created with correct permissions"

# ==============================================================================
step "4/5  Verifying configuration & restarting Tor"
# ==============================================================================

# Syntax-check the torrc before restarting
tor --verify-config -f "${TORRC}" > /dev/null 2>&1 \
    || {
        err "Tor configuration verification failed. Restoring backup..."
        if [[ -f "${TORRC_BACKUP}" ]]; then
            cp "${TORRC_BACKUP}" "${TORRC}"
            log "Backup restored. Please check your configuration manually."
        fi
        die "Invalid torrc — Tor was NOT restarted"
    }

log "torrc syntax verified successfully"

systemctl restart tor \
    || die "Failed to restart Tor. Check: journalctl -u tor --no-pager -n 30"

# Give Tor a moment to bootstrap and read keys
sleep 3

# Verify Tor is actually running
if systemctl is-active --quiet tor; then
    log "Tor restarted and running"
else
    die "Tor is not running after restart. Check: journalctl -u tor --no-pager -n 30"
fi

# ==============================================================================
step "5/5  Verifying onion addresses"
# ==============================================================================

echo ""
ALL_OK=true
INDEX=0
for dir in "${ONION_DIRS[@]}"; do
    name="$(basename "${dir}")"

    if [[ ${INDEX} -eq 0 ]]; then
        label="primary"
    else
        label="mirror_${INDEX}"
    fi

    service_dir="${TOR_DATA_DIR}/hidden_service_${label}"
    hostname_file="${service_dir}/hostname"

    if [[ -f "${hostname_file}" ]]; then
        live_address="$(cat "${hostname_file}" | tr -d '[:space:]')"
        if [[ "${live_address}" == "${name}" ]]; then
            log "[${label}] ${live_address}"
        else
            err "[${label}] Address mismatch! Expected: ${name}, Got: ${live_address}"
            ALL_OK=false
        fi
    else
        err "[${label}] hostname file missing at ${hostname_file}"
        ALL_OK=false
    fi

    ((INDEX++))
done

echo ""

if [[ "${ALL_OK}" == true ]]; then
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  Tor hidden services configured successfully!${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
else
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}  Some services have issues — review errors above${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
fi

echo ""
echo "  Useful commands:"
echo "    sudo systemctl status tor"
echo "    sudo journalctl -u tor --no-pager -n 50"
echo "    sudo cat ${TOR_DATA_DIR}/hidden_service_primary/hostname"
echo ""
