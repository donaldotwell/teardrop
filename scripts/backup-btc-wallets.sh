#!/usr/bin/env bash
# Bitcoin Core wallet backup — dumps all named wallets via bitcoin-cli, gzips, transfers via SSH.
# Usage: ./backup-btc-wallets.sh <backup-server-ip>
set -euo pipefail

BACKUP_SERVER="${1:?Usage: $0 <backup-server-ip>}"
SSH_KEY="${HOME}/.ssh/id_ed25519_backup"
SSH_USER="root"

BTC_CLI="${BTC_CLI:-bitcoin-cli}"
BTC_DATADIR="${BTC_DATADIR:-/var/lib/bitcoin}"
BTC_RPC_USER="${BITCOIN_RPC_USER:-}"
BTC_RPC_PASS="${BITCOIN_RPC_PASSWORD:-}"
BTC_RPC_PORT="${BITCOIN_RPC_PORT:-8332}"

DATE_DIR="$(date +%Y-%m)"
DATE_FILE="$(date +%Y-%m-%d)"
TIMESTAMP="$(date +%Y-%m-%dT%H-%M-%S)"
TMP_STAGE="/tmp/btc-wallets-${TIMESTAMP}"
FILENAME="btc-wallets-${TIMESTAMP}.tar.gz"
TMP_ARCHIVE="/tmp/${FILENAME}"

mkdir -p "${TMP_STAGE}"

RPC_ARGS=(-rpcport="${BTC_RPC_PORT}")
[[ -n "${BTC_RPC_USER}" ]] && RPC_ARGS+=(-rpcuser="${BTC_RPC_USER}")
[[ -n "${BTC_RPC_PASS}" ]] && RPC_ARGS+=(-rpcpassword="${BTC_RPC_PASS}")

echo "[backup-btc] Listing loaded wallets..."
WALLETS=$("${BTC_CLI}" "${RPC_ARGS[@]}" listwallets 2>/dev/null | grep -oP '(?<=")\S+(?=")' || true)

if [[ -z "${WALLETS}" ]]; then
    echo "[backup-btc] No loaded wallets found via RPC — falling back to copying wallet directory."
    tar -czf "${TMP_ARCHIVE}" -C "${BTC_DATADIR}" wallets
else
    for WALLET in ${WALLETS}; do
        DEST="${TMP_STAGE}/${WALLET}.wallet"
        echo "[backup-btc] Dumping wallet: ${WALLET}..."
        "${BTC_CLI}" "${RPC_ARGS[@]}" -rpcwallet="${WALLET}" dumpwallet "${DEST}" > /dev/null
    done
    tar -czf "${TMP_ARCHIVE}" -C "$(dirname "${TMP_STAGE}")" "$(basename "${TMP_STAGE}")"
fi

rm -rf "${TMP_STAGE}"

REMOTE_DIR="/backups/teardrop/${DATE_DIR}/${DATE_FILE}"
echo "[backup-btc] Transferring to ${BACKUP_SERVER}:${REMOTE_DIR}/${FILENAME}..."
ssh -i "${SSH_KEY}" \
    -o StrictHostKeyChecking=no \
    -o BatchMode=yes \
    "${SSH_USER}@${BACKUP_SERVER}" \
    "mkdir -p '${REMOTE_DIR}'"

scp -i "${SSH_KEY}" \
    -o StrictHostKeyChecking=no \
    "${TMP_ARCHIVE}" \
    "${SSH_USER}@${BACKUP_SERVER}:${REMOTE_DIR}/${FILENAME}"

rm -f "${TMP_ARCHIVE}"
echo "[backup-btc] Done: ${REMOTE_DIR}/${FILENAME}"
