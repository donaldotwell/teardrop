#!/usr/bin/env bash
# Monero wallet files backup — gzip and transfer via SSH.
# Usage: ./backup-xmr-wallets.sh <backup-server-ip>
set -euo pipefail

BACKUP_SERVER="${1:?Usage: $0 <backup-server-ip>}"
SSH_KEY="${HOME}/.ssh/id_ed25519_backup"
SSH_USER="root"

XMR_WALLET_DIR="${MONERO_WALLET_DIR:-/var/lib/monero/wallets}"

DATE_DIR="$(date +%Y-%m)"
DATE_FILE="$(date +%Y-%m-%d)"
TIMESTAMP="$(date +%Y-%m-%dT%H-%M-%S)"
FILENAME="xmr-wallets-${TIMESTAMP}.tar.gz"
TMP_FILE="/tmp/${FILENAME}"

echo "[backup-xmr] Archiving ${XMR_WALLET_DIR}..."
tar -czf "${TMP_FILE}" -C "$(dirname "${XMR_WALLET_DIR}")" "$(basename "${XMR_WALLET_DIR}")"

REMOTE_DIR="/backups/teardrop/${DATE_DIR}/${DATE_FILE}"
echo "[backup-xmr] Transferring to ${BACKUP_SERVER}:${REMOTE_DIR}/${FILENAME}..."
ssh -i "${SSH_KEY}" \
    -o StrictHostKeyChecking=no \
    -o BatchMode=yes \
    "${SSH_USER}@${BACKUP_SERVER}" \
    "mkdir -p '${REMOTE_DIR}'"

scp -i "${SSH_KEY}" \
    -o StrictHostKeyChecking=no \
    "${TMP_FILE}" \
    "${SSH_USER}@${BACKUP_SERVER}:${REMOTE_DIR}/${FILENAME}"

rm -f "${TMP_FILE}"
echo "[backup-xmr] Done: ${REMOTE_DIR}/${FILENAME}"
