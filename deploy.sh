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
#
# 共有レンタルサーバーでは SSH の `php` / `composer` コマンドが本番の対象
# バージョンと異なることがある(Xserver は `php` が既定で古いバージョンを
# 指し、ドメインの PHP バージョン切替は Web 実行にしか反映されない)。
# その場合は環境変数で明示的に指定する:
#   PHP_BIN=php8.4 ./deploy.sh
set -euo pipefail

cd "$(dirname "$0")"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

echo "==> メンテナンスモードへ切り替え"
"$PHP_BIN" artisan down --render="errors::503" --retry=15 || true

# 何が起きてもメンテナンスモードを解除してから終了する。
cleanup() {
  echo "==> メンテナンスモードを解除"
  "$PHP_BIN" artisan up
}
trap cleanup EXIT

echo "==> git pull"
git pull origin master

echo "==> composer install(本番用)"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader

echo "==> マイグレーション"
"$PHP_BIN" artisan migrate --force

echo "==> キャッシュの再構築"
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache

# キューは cron から毎分 `queue:work --stop-when-empty` を実行する運用で、
# 常駐ワーカーが存在しない(TASKS.md T-15)。次の cron 実行時点で自動的に
# 新しいコードで動くため、ここでの queue:restart は不要。
echo "==> デプロイ完了: $(date '+%Y-%m-%d %H:%M:%S')"
