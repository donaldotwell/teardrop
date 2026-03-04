#!/usr/bin/env bash
# ==============================================================================
# Teardrop — Tor Hidden Service Configuration
# Reads vanity .onion address keys from a source directory and configures
# Tor hidden services with proper permissions and torrc entries.
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

# Return a label for the Nth service (0-indexed)
service_label() {
    local idx=$1
    if [[ ${idx} -eq 0 ]]; then
        echo "primary"
    else
        echo "mirror_${idx}"
    fi
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
done

if [[ ${#ONION_DIRS[@]} -eq 0 ]]; then
    die "No valid .onion address directories found in ${ADDRESSES_DIR}"
fi

info "Found ${#ONION_DIRS[@]} valid onion address(es) (${SKIP_COUNT} skipped)"
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
    touch "${TORRC}"
fi

# ==============================================================================
step "2/5  Writing Tor configuration (atomic rebuild)"
# ==============================================================================
# Strategy: build the new torrc in a temp file, verify it with tor, then move
# it into place atomically. This avoids in-place sed edits that corrupt the
# file on partial runs or repeated executions.

TORRC_TMP="$(mktemp "${TORRC}.tmp.XXXXXX")"
trap 'rm -f "${TORRC_TMP}"' EXIT   # cleanup temp file on any exit

# --- Extract user content (everything outside our managed block) ---
# awk skips the managed block (START→END inclusive) and any orphaned
# HiddenService / comment lines our block would have generated.
awk -v start="${BLOCK_START}" -v end="${BLOCK_END}" '
    $0 == start { skip=1; next }
    $0 == end   { skip=0; next }
    skip        { next }
    /^HiddenServiceDir /    { next }
    /^HiddenServicePort /   { next }
    /^HiddenServiceVersion /{ next }
    /^# (primary|mirror_[0-9]+): /{ next }
    { print }
' "${TORRC}" > "${TORRC_TMP}"

# --- Ensure base Tor settings (idempotent: replace if present, append if not) ---
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

    for i in "${!ONION_DIRS[@]}"; do
        dir="${ONION_DIRS[$i]}"
        name="$(basename "${dir}")"
        label="$(service_label "$i")"
        service_dir="${TOR_DATA_DIR}/hidden_service_${label}"

        echo "# ${label}: ${name}"
        echo "HiddenServiceDir ${service_dir}/"
        echo "HiddenServicePort ${LISTEN_PORT} ${LISTEN_ADDR}:${LISTEN_PORT}"
        echo "HiddenServiceVersion ${HIDDEN_SERVICE_VERSION}"
        echo ""
    done

    echo "${BLOCK_END}"
} >> "${TORRC_TMP}"

log "Generated config with ${#ONION_DIRS[@]} hidden service(s)"

# --- Debug: show what we built ---
info "Hidden service blocks in new torrc:"
grep -E '^(HiddenServiceDir|# (primary|mirror))' "${TORRC_TMP}" | while IFS= read -r line; do
    info "  ${line}"
done

# ==============================================================================
step "3/5  Creating hidden service directories & copying keys"
# ==============================================================================

for i in "${!ONION_DIRS[@]}"; do
    dir="${ONION_DIRS[$i]}"
    name="$(basename "${dir}")"
    label="$(service_label "$i")"
    service_dir="${TOR_DATA_DIR}/hidden_service_${label}"

    mkdir -p "${service_dir}"

    cp "${dir}hostname"                "${service_dir}/hostname"
    cp "${dir}hs_ed25519_public_key"   "${service_dir}/hs_ed25519_public_key"
    cp "${dir}hs_ed25519_secret_key"   "${service_dir}/hs_ed25519_secret_key"

    # Tor requires 700 on dir, 600 on key files
    chown -R "${TOR_USER}:${TOR_GROUP}" "${service_dir}"
    chmod 700 "${service_dir}"
    chmod 600 "${service_dir}/hs_ed25519_secret_key"
    chmod 600 "${service_dir}/hs_ed25519_public_key"
    chmod 644 "${service_dir}/hostname"

    log "[${label}] Keys deployed → ${service_dir}/"
done

chown "${TOR_USER}:${TOR_GROUP}" "${TOR_DATA_DIR}"
chmod 700 "${TOR_DATA_DIR}"

log "All hidden service directories created with correct permissions"

# ==============================================================================
step "4/5  Verifying configuration & restarting Tor"
# ==============================================================================

# Validate the NEW config BEFORE moving it into place
tor --verify-config -f "${TORRC_TMP}" > /dev/null 2>&1 \
    || {
        err "Tor configuration verification FAILED on new config"
        err "Temp file preserved for inspection: ${TORRC_TMP}"
        trap - EXIT   # keep the tmp file for debugging
        die "Invalid torrc — original ${TORRC} was NOT modified"
    }

log "New torrc syntax verified successfully"

# Atomic replace: move verified temp file over the real torrc
mv "${TORRC_TMP}" "${TORRC}"
chmod 644 "${TORRC}"
trap - EXIT   # disarm cleanup — file has been moved successfully

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
step "5/5  Verifying onion addresses"
# ==============================================================================

echo ""
ALL_OK=true
for i in "${!ONION_DIRS[@]}"; do
    dir="${ONION_DIRS[$i]}"
    name="$(basename "${dir}")"
    label="$(service_label "$i")"
    hostname_file="${TOR_DATA_DIR}/hidden_service_${label}/hostname"

    if [[ -f "${hostname_file}" ]]; then
        live_address="$(tr -d '[:space:]' < "${hostname_file}")"
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
