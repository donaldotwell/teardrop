#!/usr/bin/env bash
# PostgreSQL database backup — gzip and transfer via SSH.
# Usage: ./backup-db.sh <backup-server-ip>
set -euo pipefail

BACKUP_SERVER="${1:?Usage: $0 <backup-server-ip>}"
SSH_KEY="${HOME}/.ssh/id_ed25519_backup"
SSH_USER="root"

DB_NAME="${DB_DATABASE:-teardrop}"
DB_USER="${DB_USERNAME:-postgres}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_PASS="${DB_PASSWORD:-}"

DATE_DIR="$(date +%Y-%m)"
DATE_FILE="$(date +%Y-%m-%d)"
TIMESTAMP="$(date +%Y-%m-%dT%H-%M-%S)"
FILENAME="db-${DB_NAME}-${TIMESTAMP}.sql.gz"
TMP_FILE="/tmp/${FILENAME}"

echo "[backup-db] Dumping ${DB_NAME}..."
PGPASSWORD="${DB_PASS}" pg_dump \
    -h "${DB_HOST}" \
    -p "${DB_PORT}" \
    -U "${DB_USER}" \
    --no-owner \
    --no-acl \
    "${DB_NAME}" | gzip -9 > "${TMP_FILE}"

REMOTE_DIR="/backups/teardrop/${DATE_DIR}/${DATE_FILE}"
echo "[backup-db] Transferring to ${BACKUP_SERVER}:${REMOTE_DIR}/${FILENAME}..."
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
echo "[backup-db] Done: ${REMOTE_DIR}/${FILENAME}"
