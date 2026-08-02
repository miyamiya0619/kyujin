#!/usr/bin/env bash
# 日次バックアップ(TASKS.md T-18 / SPEC.md 13章)。
#
# Xserver の自動バックアップ(サーバ内保管・直近数日分)に加えて、
# mysqldump を取得し環境外(別ストレージ)へ退避する。サーバ側の障害と
# 誤操作(例: data:destroy-all の実行ミス)の両方に備えるための二重化。
#
# Laravel 本体のルートディレクトリ(artisan と同じ階層)で実行する。
# 退避先は環境変数 BACKUP_DEST_DIR で指定する(未設定ならローカル保管のみ)。
# 退避先の用意(rclone でマウントした外部ストレージ、別 Xserver アカウントの
# 共有ディレクトリ等)は運用者が別途行うこと。
#
# 使い方:
#   BACKUP_DEST_DIR=/path/to/offsite ./backup.sh
#   (cron から毎日 1 回実行する想定)
set -euo pipefail

cd "$(dirname "$0")"
set -a
source .env
set +a

TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_DIR="storage/app/private/backups"
BACKUP_FILE="${BACKUP_DIR}/${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"

mysqldump \
  --host="${DB_HOST}" --port="${DB_PORT}" \
  --user="${DB_USERNAME}" --password="${DB_PASSWORD}" \
  --single-transaction --quick \
  "${DB_DATABASE}" | gzip > "${BACKUP_FILE}"

echo "バックアップを作成しました: ${BACKUP_FILE}"

if [ -n "${BACKUP_DEST_DIR:-}" ]; then
  cp "${BACKUP_FILE}" "${BACKUP_DEST_DIR}/"
  echo "環境外へ退避しました: ${BACKUP_DEST_DIR}/${TIMESTAMP}.sql.gz"
else
  echo "警告: BACKUP_DEST_DIR が未設定のため環境外への退避を行っていません。" >&2
fi

# ローカル保管は直近 14 日分だけ残す(ディスク容量の上限があるため)。
find "${BACKUP_DIR}" -name "*.sql.gz" -mtime +14 -delete
