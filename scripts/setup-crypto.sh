#!/usr/bin/env bash
# ==============================================================================
# Teardrop — Bitcoin Core & Monero Wallet RPC Setup Script
#
# Installs bitcoind and monero-wallet-rpc as hardened systemd services,
# each running under their own dedicated system user.
#
# Idempotent — safe to re-run after a failure. Downloads are cached in
# /var/cache/teardrop-crypto and resumed automatically if interrupted.
#
# Usage:
#   BITCOIN_RPC_USER=foo BITCOIN_RPC_PASS=bar \
#   MONERO_RPC_USER=baz  MONERO_RPC_PASS=qux \
#   sudo -E ./setup-crypto.sh
#
# Optional overrides (export before running):
#   BITCOIN_VERSION   default: 27.1
#   MONERO_VERSION    default: 0.18.3.4
#   BITCOIN_NETWORK   mainnet | testnet   (default: mainnet)
#   MONERO_NETWORK    mainnet | stagenet  (default: mainnet)
#   MONERO_WALLET_DIR directory for per-user wallets (default: /var/lib/monero/wallets)
# ==============================================================================
set -euo pipefail

# ========================= Configuration ======================================
BITCOIN_VERSION="${BITCOIN_VERSION:-27.1}"
MONERO_VERSION="${MONERO_VERSION:-0.18.3.4}"

BITCOIN_USER="${BITCOIN_USER:-bitcoin}"
MONERO_USER="${MONERO_USER:-monero}"

BITCOIN_RPC_USER="${BITCOIN_RPC_USER:-}"
BITCOIN_RPC_PASS="${BITCOIN_RPC_PASS:-}"
MONERO_RPC_USER="${MONERO_RPC_USER:-}"
MONERO_RPC_PASS="${MONERO_RPC_PASS:-}"

BITCOIN_NETWORK="${BITCOIN_NETWORK:-mainnet}"
MONERO_NETWORK="${MONERO_NETWORK:-mainnet}"

BITCOIN_DATA_DIR="/var/lib/bitcoin"
MONERO_DATA_DIR="/var/lib/monero"
MONERO_WALLET_DIR="${MONERO_WALLET_DIR:-/var/lib/monero/wallets}"

BITCOIN_RPC_PORT=8332
[[ "${BITCOIN_NETWORK}" == "testnet" ]] && BITCOIN_RPC_PORT=18332

MONERO_DAEMON_PORT=18081
[[ "${MONERO_NETWORK}" == "stagenet" ]] && MONERO_DAEMON_PORT=38081

MONERO_RPC_PORT=28088

INSTALL_DIR="/usr/local/bin"
CACHE_DIR="/var/cache/teardrop-crypto"
EXTRACT_DIR="$(mktemp -d)"
trap 'rm -rf "${EXTRACT_DIR}"' EXIT

mkdir -p "${CACHE_DIR}"

# ========================= Helpers ============================================
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GREEN}[✔]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
die()  { echo -e "${RED}[✘]${NC} $*"; exit 1; }
step() {
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  $*${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# download DEST URL
# Resumes partial downloads. Skips if file already exists.
download() {
    local dest="$1" url="$2"
    if [[ -f "${dest}" ]]; then
        log "$(basename "${dest}") already downloaded — skipping"
        return 0
    fi
    wget -c --show-progress \
        --connect-timeout=30 --read-timeout=120 --tries=3 \
        -O "${dest}.part" "${url}" \
        || { rm -f "${dest}.part"; die "Failed to download $(basename "${dest}")"; }
    mv "${dest}.part" "${dest}"
    log "$(basename "${dest}") downloaded"
}

# ========================= Preflight ==========================================
[[ $EUID -ne 0 ]]              && die "Must run as root: sudo -E ./setup-crypto.sh"
[[ -z "${BITCOIN_RPC_USER}" ]] && die "BITCOIN_RPC_USER is required"
[[ -z "${BITCOIN_RPC_PASS}" ]] && die "BITCOIN_RPC_PASS is required"
[[ -z "${MONERO_RPC_USER}" ]]  && die "MONERO_RPC_USER is required"
[[ -z "${MONERO_RPC_PASS}" ]]  && die "MONERO_RPC_PASS is required"

ARCH="$(uname -m)"
case "${ARCH}" in
    x86_64)  BTC_ARCH="x86_64-linux-gnu"; XMR_ARCH="linux-x64" ;;
    aarch64) BTC_ARCH="aarch64-linux-gnu"; XMR_ARCH="linux-armv8" ;;
    *) die "Unsupported architecture: ${ARCH}" ;;
esac

# ==============================================================================
step "1/9  Installing dependencies"
# ==============================================================================
apt-get update -qq
apt-get install -y --no-install-recommends \
    wget tar bzip2 \
    || die "Failed to install dependencies"
log "Dependencies installed"

# ==============================================================================
step "2/9  Creating system users"
# ==============================================================================
for SVC_USER in "${BITCOIN_USER}" "${MONERO_USER}"; do
    if id "${SVC_USER}" &>/dev/null; then
        warn "User '${SVC_USER}' already exists — skipping"
    else
        useradd --system --no-create-home --shell /usr/sbin/nologin "${SVC_USER}" \
            || die "Failed to create user ${SVC_USER}"
        log "User '${SVC_USER}' created"
    fi
done

# ==============================================================================
step "3/9  Downloading & installing Bitcoin Core ${BITCOIN_VERSION}"
# ==============================================================================
BTC_TARBALL="bitcoin-${BITCOIN_VERSION}-${BTC_ARCH}.tar.gz"
BTC_URL="https://bitcoincore.org/bin/bitcoin-core-${BITCOIN_VERSION}/${BTC_TARBALL}"

if [[ -x "${INSTALL_DIR}/bitcoind" ]] \
    && "${INSTALL_DIR}/bitcoind" --version 2>/dev/null | grep -qF "${BITCOIN_VERSION}"; then
    log "Bitcoin Core ${BITCOIN_VERSION} already installed — skipping"
else
    download "${CACHE_DIR}/${BTC_TARBALL}" "${BTC_URL}"
    tar -xzf "${CACHE_DIR}/${BTC_TARBALL}" -C "${EXTRACT_DIR}"
    install -m 755 "${EXTRACT_DIR}/bitcoin-${BITCOIN_VERSION}/bin/bitcoind"    "${INSTALL_DIR}/bitcoind"
    install -m 755 "${EXTRACT_DIR}/bitcoin-${BITCOIN_VERSION}/bin/bitcoin-cli" "${INSTALL_DIR}/bitcoin-cli"
    log "Bitcoin Core ${BITCOIN_VERSION} installed to ${INSTALL_DIR}"
fi

# ==============================================================================
step "4/9  Configuring Bitcoin Core"
# ==============================================================================
mkdir -p "${BITCOIN_DATA_DIR}"
chown "${BITCOIN_USER}:${BITCOIN_USER}" "${BITCOIN_DATA_DIR}"
chmod 750 "${BITCOIN_DATA_DIR}"

BITCOIN_CONF="${BITCOIN_DATA_DIR}/bitcoin.conf"

BTC_NETWORK_CONF=""
[[ "${BITCOIN_NETWORK}" == "testnet" ]] && BTC_NETWORK_CONF="testnet=1"

cat > "${BITCOIN_CONF}" <<EOF
# Network
${BTC_NETWORK_CONF}
listen=1
maxconnections=40

# RPC — bound to localhost only
server=1
rpcbind=127.0.0.1
rpcallowip=127.0.0.1
rpcport=${BITCOIN_RPC_PORT}
rpcuser=${BITCOIN_RPC_USER}
rpcpassword=${BITCOIN_RPC_PASS}

# Wallet
disablewallet=0
walletbroadcast=1
fallbackfee=0.00001

# Performance
dbcache=512
maxmempool=300

# Logging
logtimestamps=1
debug=0
EOF

chown "${BITCOIN_USER}:${BITCOIN_USER}" "${BITCOIN_CONF}"
chmod 640 "${BITCOIN_CONF}"
log "Bitcoin config written to ${BITCOIN_CONF}"

# ==============================================================================
step "5/9  Creating bitcoind systemd service"
# ==============================================================================
# NOTE: No -daemon flag. systemd manages the process lifecycle directly.
cat > /etc/systemd/system/bitcoind.service <<EOF
[Unit]
Description=Bitcoin Core
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=${BITCOIN_USER}
Group=${BITCOIN_USER}

ExecStart=${INSTALL_DIR}/bitcoind -datadir=${BITCOIN_DATA_DIR} -conf=${BITCOIN_CONF}
ExecStop=${INSTALL_DIR}/bitcoin-cli -datadir=${BITCOIN_DATA_DIR} -conf=${BITCOIN_CONF} stop

Restart=on-failure
RestartSec=30
TimeoutStartSec=infinity
TimeoutStopSec=600

# Hardening
PrivateTmp=true
ProtectSystem=full
ProtectHome=true
NoNewPrivileges=true
MemoryDenyWriteExecute=false

[Install]
WantedBy=multi-user.target
EOF

log "bitcoind.service written"

# ==============================================================================
step "6/9  Downloading & installing Monero ${MONERO_VERSION}"
# ==============================================================================
XMR_TARBALL="monero-${XMR_ARCH}-v${MONERO_VERSION}.tar.bz2"
XMR_URL="https://downloads.getmonero.org/cli/${XMR_TARBALL}"

if [[ -x "${INSTALL_DIR}/monerod" ]] \
    && "${INSTALL_DIR}/monerod" --version 2>/dev/null | grep -qF "${MONERO_VERSION}"; then
    log "Monero ${MONERO_VERSION} already installed — skipping"
else
    download "${CACHE_DIR}/${XMR_TARBALL}" "${XMR_URL}"
    tar -xjf "${CACHE_DIR}/${XMR_TARBALL}" -C "${EXTRACT_DIR}"
    XMR_EXTRACT_DIR="$(find "${EXTRACT_DIR}" -maxdepth 1 -type d -name 'monero-*' | head -1)"
    [[ -z "${XMR_EXTRACT_DIR}" ]] && die "Could not find extracted Monero directory"
    install -m 755 "${XMR_EXTRACT_DIR}/monerod"           "${INSTALL_DIR}/monerod"
    install -m 755 "${XMR_EXTRACT_DIR}/monero-wallet-rpc" "${INSTALL_DIR}/monero-wallet-rpc"
    log "Monero ${MONERO_VERSION} installed to ${INSTALL_DIR}"
fi

# ==============================================================================
step "7/9  Configuring Monero"
# ==============================================================================
MONERO_RINGDB_DIR="${MONERO_DATA_DIR}/ringdb"

mkdir -p "${MONERO_DATA_DIR}" "${MONERO_WALLET_DIR}" "${MONERO_RINGDB_DIR}"
chown -R "${MONERO_USER}:${MONERO_USER}" "${MONERO_DATA_DIR}" "${MONERO_WALLET_DIR}"
chmod 750 "${MONERO_DATA_DIR}" "${MONERO_WALLET_DIR}" "${MONERO_RINGDB_DIR}"

MONEROD_CONF="${MONERO_DATA_DIR}/monerod.conf"

XMR_NETWORK_FLAG=""
[[ "${MONERO_NETWORK}" == "stagenet" ]] && XMR_NETWORK_FLAG="--stagenet"

cat > "${MONEROD_CONF}" <<EOF
# Data
data-dir=${MONERO_DATA_DIR}

# RPC — localhost only, restricted
rpc-bind-ip=127.0.0.1
rpc-bind-port=${MONERO_DAEMON_PORT}
restricted-rpc=1
confirm-external-bind=0

# Network
no-igd=1
no-zmq=1
max-connections-per-ip=1

# Logging
log-level=0
max-log-file-size=10485760
EOF

chown "${MONERO_USER}:${MONERO_USER}" "${MONEROD_CONF}"
chmod 640 "${MONEROD_CONF}"
log "monerod config written to ${MONEROD_CONF}"

# ==============================================================================
step "8/9  Creating monerod & monero-wallet-rpc systemd services"
# ==============================================================================

# NOTE: No --detach flag. systemd manages the process lifecycle directly.
cat > /etc/systemd/system/monerod.service <<EOF
[Unit]
Description=Monero Daemon
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=${MONERO_USER}
Group=${MONERO_USER}

ExecStart=${INSTALL_DIR}/monerod ${XMR_NETWORK_FLAG} --config-file=${MONEROD_CONF} --non-interactive
ExecStop=${INSTALL_DIR}/monerod ${XMR_NETWORK_FLAG} --config-file=${MONEROD_CONF} exit

Restart=on-failure
RestartSec=30
TimeoutStartSec=infinity
TimeoutStopSec=120

# Hardening
PrivateTmp=true
ProtectSystem=full
ProtectHome=true
NoNewPrivileges=true

[Install]
WantedBy=multi-user.target
EOF

log "monerod.service written"

# NOTE: No --detach flag. Wallet RPC starts after monerod is up.
cat > /etc/systemd/system/monero-wallet-rpc.service <<EOF
[Unit]
Description=Monero Wallet RPC
After=monerod.service
Wants=monerod.service

[Service]
Type=simple
User=${MONERO_USER}
Group=${MONERO_USER}

ExecStart=${INSTALL_DIR}/monero-wallet-rpc \
    ${XMR_NETWORK_FLAG} \
    --wallet-dir=${MONERO_WALLET_DIR} \
    --rpc-bind-ip=127.0.0.1 \
    --rpc-bind-port=${MONERO_RPC_PORT} \
    --rpc-login=${MONERO_RPC_USER}:${MONERO_RPC_PASS} \
    --daemon-address=127.0.0.1:${MONERO_DAEMON_PORT} \
    --trusted-daemon \
    --shared-ringdb-dir=${MONERO_DATA_DIR}/ringdb \
    --log-level=1 \
    --max-log-file-size=10485760

Restart=on-failure
RestartSec=15

# Hardening
PrivateTmp=true
ProtectSystem=full
ProtectHome=true
NoNewPrivileges=true

[Install]
WantedBy=multi-user.target
EOF

log "monero-wallet-rpc.service written"

# ==============================================================================
step "9/9  Enabling & starting services"
# ==============================================================================
systemctl daemon-reload

systemctl enable --now bitcoind           || warn "Could not enable bitcoind"
systemctl enable --now monerod            || warn "Could not enable monerod"
systemctl enable --now monero-wallet-rpc  || warn "Could not enable monero-wallet-rpc"

log "All crypto services enabled and started"

# ==============================================================================
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  Setup complete!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  Add these to your Laravel .env:"
echo ""
echo "  BITCOIN_RPC_SCHEME=http"
echo "  BITCOIN_RPC_HOST=127.0.0.1"
echo "  BITCOIN_RPC_PORT=${BITCOIN_RPC_PORT}"
echo "  BITCOIN_RPC_USER=${BITCOIN_RPC_USER}"
echo "  BITCOIN_RPC_PASSWORD=${BITCOIN_RPC_PASS}"
echo "  BITCOIN_NETWORK=${BITCOIN_NETWORK}"
echo ""
echo "  MONERO_RPC_SCHEME=http"
echo "  MONERO_RPC_HOST=127.0.0.1"
echo "  MONERO_RPC_PORT=${MONERO_RPC_PORT}"
echo "  MONERO_RPC_USER=${MONERO_RPC_USER}"
echo "  MONERO_RPC_PASSWORD=${MONERO_RPC_PASS}"
echo "  MONERO_WALLET_DIR=${MONERO_WALLET_DIR}"
echo "  MONERO_NETWORK=${MONERO_NETWORK}"
echo ""
echo "  Service status:"
echo "    systemctl status bitcoind"
echo "    systemctl status monerod"
echo "    systemctl status monero-wallet-rpc"
echo ""
echo "  Logs:"
echo "    journalctl -fu bitcoind"
echo "    journalctl -fu monerod"
echo "    journalctl -fu monero-wallet-rpc"
echo ""
echo "  Download cache (safe to delete after successful run):"
echo "    ${CACHE_DIR}"
echo ""
