#!/usr/bin/env bash
# ==============================================================================
# Teardrop — Tor Hidden Service Configuration
# Reads vanity .onion address keys from a source directory and configures
# Tor hidden services with proper permissions and torrc entries.
#
# Hidden service directories are named after their .onion address, e.g.:
#   /var/lib/tor/abc...xyz.onion/
#
# Must be executed as root or via sudo.
# Safe to run multiple times — fully idempotent (temp-file + atomic move).
#
# Usage:
#   sudo ./configure-tor.sh
#   ADDRESSES_DIR=/path/to/keys sudo -E ./configure-tor.sh
# ==============================================================================
set -euo pipefail
shopt -s nullglob   # globs that match nothing expand to nothing, not themselves

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

# Markers that delimit our managed block inside torrc
BLOCK_START="## --- TEARDROP HIDDEN SERVICES START ---"
BLOCK_END="## --- TEARDROP HIDDEN SERVICES END ---"

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

# ========================= Discover addresses =================================
ONION_DIRS=()
ONION_NAMES=()
SKIP_COUNT=0

dirs=("${ADDRESSES_DIR}"/*/)
if [[ ${#dirs[@]} -eq 0 ]]; then
    die "No subdirectories found in ${ADDRESSES_DIR}"
fi

for dir in "${dirs[@]}"; do
    name="$(basename "${dir}")"

    # Validate v3 onion format: 56 base32 chars + .onion
    if [[ ! "${name}" =~ ^[a-z2-7]{56}\.onion$ ]]; then
        warn "Skipping '${name}' — does not match v3 .onion address format"
        SKIP_COUNT=$((SKIP_COUNT + 1))
        continue
    fi

    # Verify required key files
    missing=()
    for f in hostname hs_ed25519_public_key hs_ed25519_secret_key; do
        [[ -f "${dir}${f}" ]] || missing+=("${f}")
    done

    if [[ ${#missing[@]} -gt 0 ]]; then
        warn "Skipping '${name}' — missing: ${missing[*]}"
        SKIP_COUNT=$((SKIP_COUNT + 1))
        continue
    fi

    # Verify hostname content matches directory name
    hostname_content="$(tr -d '[:space:]' < "${dir}hostname")"
    if [[ "${hostname_content}" != "${name}" ]]; then
        warn "Skipping '${name}' — hostname file contains '${hostname_content}' (mismatch)"
        SKIP_COUNT=$((SKIP_COUNT + 1))
        continue
    fi

    ONION_DIRS+=("${dir}")
    ONION_NAMES+=("${name}")
done

if [[ ${#ONION_DIRS[@]} -eq 0 ]]; then
    die "No valid .onion address directories found in ${ADDRESSES_DIR}"
fi

info "Found ${#ONION_DIRS[@]} valid onion address(es) (${SKIP_COUNT} skipped)"
echo ""
for name in "${ONION_NAMES[@]}"; do
    info "  ${name}"
done

# ==============================================================================
step "1/6  Backing up current torrc"
# ==============================================================================
if [[ -f "${TORRC}" ]]; then
    cp "${TORRC}" "${TORRC_BACKUP}"
    log "Backed up ${TORRC} → ${TORRC_BACKUP}"
else
    warn "No existing torrc found at ${TORRC} — will create a new one"
    touch "${TORRC}"
fi

# ==============================================================================
step "2/6  Cleaning up old hidden service directories"
# ==============================================================================
# Remove legacy hidden_service_primary / hidden_service_mirror_* directories
# from previous runs that used label-based naming.
OLD_DIRS=("${TOR_DATA_DIR}"/hidden_service_primary/ "${TOR_DATA_DIR}"/hidden_service_mirror_*/)
for old_dir in "${OLD_DIRS[@]}"; do
    if [[ -d "${old_dir}" ]]; then
        rm -rf "${old_dir}"
        log "Removed legacy directory: ${old_dir}"
    fi
done

# Remove hidden service directories for onion addresses that are no longer
# in our source list (handles address rotation / key removal).
for existing_dir in "${TOR_DATA_DIR}"/*.onion/; do
    [[ -d "${existing_dir}" ]] || continue
    existing_name="$(basename "${existing_dir}")"

    found=false
    for name in "${ONION_NAMES[@]}"; do
        if [[ "${name}" == "${existing_name}" ]]; then
            found=true
            break
        fi
    done

    if [[ "${found}" == false ]]; then
        rm -rf "${existing_dir}"
        warn "Removed stale directory: ${existing_dir}"
    fi
done

log "Cleanup complete"

# ==============================================================================
step "3/6  Creating hidden service directories & copying keys"
# ==============================================================================
# Directories are named after the full .onion address, e.g.:
#   /var/lib/tor/abc...xyz.onion/

for i in "${!ONION_DIRS[@]}"; do
    dir="${ONION_DIRS[$i]}"
    name="${ONION_NAMES[$i]}"
    service_dir="${TOR_DATA_DIR}/${name}"

    mkdir -p "${service_dir}"

    cp "${dir}hostname"                "${service_dir}/hostname"
    cp "${dir}hs_ed25519_public_key"   "${service_dir}/hs_ed25519_public_key"
    cp "${dir}hs_ed25519_secret_key"   "${service_dir}/hs_ed25519_secret_key"

    # Tor requires 700 on the dir, 600 on key files
    chown -R "${TOR_USER}:${TOR_GROUP}" "${service_dir}"
    chmod 700 "${service_dir}"
    chmod 600 "${service_dir}/hs_ed25519_secret_key"
    chmod 600 "${service_dir}/hs_ed25519_public_key"
    chmod 644 "${service_dir}/hostname"

    log "Keys deployed → ${service_dir}/"
done

# Ensure parent tor data directory ownership is correct
chown "${TOR_USER}:${TOR_GROUP}" "${TOR_DATA_DIR}"
chmod 700 "${TOR_DATA_DIR}"

log "All hidden service directories created with correct permissions"

# ==============================================================================
step "4/6  Writing Tor configuration (atomic rebuild)"
# ==============================================================================
# Strategy: build the new torrc in a temp file, verify it as the tor user,
# then move it into place atomically.

TORRC_TMP="$(mktemp "${TORRC}.tmp.XXXXXX")"
trap 'rm -f "${TORRC_TMP}"' EXIT

# --- Extract user content (everything outside our managed block) ---
awk -v start="${BLOCK_START}" -v end="${BLOCK_END}" '
    $0 == start { skip=1; next }
    $0 == end   { skip=0; next }
    skip        { next }
    /^HiddenServiceDir /    { next }
    /^HiddenServicePort /   { next }
    /^HiddenServiceVersion /{ next }
    { print }
' "${TORRC}" > "${TORRC_TMP}"

# --- Ensure base Tor settings ---
set_torrc_key() {
    local key="$1" value="$2" file="$3"
    if grep -q "^${key} " "${file}" 2>/dev/null; then
        sed -i "s|^${key} .*|${key} ${value}|" "${file}"
    else
        echo "${key} ${value}" >> "${file}"
    fi
}

set_torrc_key "SocksPort"            "9050" "${TORRC_TMP}"
set_torrc_key "ControlPort"          "9051" "${TORRC_TMP}"
set_torrc_key "CookieAuthentication" "1"    "${TORRC_TMP}"

log "Base Tor settings ensured (SocksPort, ControlPort, CookieAuthentication)"

# --- Append hidden-service blocks inside markers ---
{
    echo ""
    echo "${BLOCK_START}"
    echo "## Managed by configure-tor.sh — do not edit manually"
    echo ""

    for i in "${!ONION_NAMES[@]}"; do
        name="${ONION_NAMES[$i]}"
        service_dir="${TOR_DATA_DIR}/${name}"

        echo "# Service: ${name}"
        echo "HiddenServiceDir ${service_dir}/"
        echo "HiddenServicePort ${LISTEN_PORT} ${LISTEN_ADDR}:${LISTEN_PORT}"
        echo "HiddenServiceVersion ${HIDDEN_SERVICE_VERSION}"
        echo ""
    done

    echo "${BLOCK_END}"
} >> "${TORRC_TMP}"

log "Generated torrc with ${#ONION_NAMES[@]} hidden service(s)"

# --- Debug: show what we built ---
info "Hidden service entries:"
for name in "${ONION_NAMES[@]}"; do
    info "  HiddenServiceDir ${TOR_DATA_DIR}/${name}/"
done

# ==============================================================================
step "5/6  Verifying configuration & restarting Tor"
# ==============================================================================

# The temp file must be readable by the tor user for --verify-config
chmod 644 "${TORRC_TMP}"

# Run verify-config as the tor user so it can access its own directories.
# This avoids the "not owned by this user (root)" error.
sudo -u "${TOR_USER}" tor --verify-config -f "${TORRC_TMP}" > /dev/null 2>&1 \
    || {
        err "Tor configuration verification FAILED"
        err "Temp file preserved for inspection: ${TORRC_TMP}"
        # Show the actual error for debugging
        echo ""
        sudo -u "${TOR_USER}" tor --verify-config -f "${TORRC_TMP}" 2>&1 | tail -20 || true
        echo ""
        trap - EXIT   # keep the tmp file for debugging
        die "Invalid torrc — original ${TORRC} was NOT modified"
    }

log "New torrc syntax verified (as ${TOR_USER})"

# Atomic replace: move verified temp file over the real torrc
mv "${TORRC_TMP}" "${TORRC}"
chmod 644 "${TORRC}"
trap - EXIT   # disarm cleanup — file has been moved

log "torrc updated atomically"

systemctl restart tor \
    || die "Failed to restart Tor. Check: journalctl -u tor --no-pager -n 30"

sleep 3

if systemctl is-active --quiet tor; then
    log "Tor restarted and running"
else
    die "Tor is not running after restart. Check: journalctl -u tor --no-pager -n 30"
fi

# ==============================================================================
step "6/6  Verifying onion addresses"
# ==============================================================================

echo ""
ALL_OK=true
for i in "${!ONION_NAMES[@]}"; do
    name="${ONION_NAMES[$i]}"
    hostname_file="${TOR_DATA_DIR}/${name}/hostname"

    if [[ -f "${hostname_file}" ]]; then
        live_address="$(tr -d '[:space:]' < "${hostname_file}")"
        if [[ "${live_address}" == "${name}" ]]; then
            log "${live_address}"
        else
            err "Address mismatch! Expected: ${name}, Got: ${live_address}"
            ALL_OK=false
        fi
    else
        err "hostname file missing at ${hostname_file}"
        ALL_OK=false
    fi
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
for name in "${ONION_NAMES[@]}"; do
    echo "    sudo cat ${TOR_DATA_DIR}/${name}/hostname"
done
echo ""
