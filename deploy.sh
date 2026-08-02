#!/usr/bin/env bash
# 本番環境(エックスサーバー)へのデプロイ。
# Laravel 本体のルートディレクトリ(artisan と同じ階層)で実行する(TASKS.md T-18)。
#
# 前提:
#   - git remote が設定済みで、対象ブランチを pull できること
#   - .env が本番用に配置済みであること(このスクリプトは .env を書き換えない)
#   - public/build はリポジトリにコミット済み(本番に Node.js が無いためサーバではビルドしない。
#     CLAUDE.md 4章)。ローカルで `npm run build` してからコミット・push すること
#
# 使い方:
#   ./deploy.sh
set -euo pipefail

cd "$(dirname "$0")"

echo "==> メンテナンスモードへ切り替え"
php artisan down --render="errors::503" --retry=15 || true

# 何が起きてもメンテナンスモードを解除してから終了する。
cleanup() {
  echo "==> メンテナンスモードを解除"
  php artisan up
}
trap cleanup EXIT

echo "==> git pull"
git pull origin main

echo "==> composer install(本番用)"
composer install --no-dev --optimize-autoloader

echo "==> マイグレーション"
php artisan migrate --force

echo "==> キャッシュの再構築"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# キューは cron から毎分 `queue:work --stop-when-empty` を実行する運用で、
# 常駐ワーカーが存在しない(TASKS.md T-15)。次の cron 実行時点で自動的に
# 新しいコードで動くため、ここでの queue:restart は不要。
echo "==> デプロイ完了: $(date '+%Y-%m-%d %H:%M:%S')"
