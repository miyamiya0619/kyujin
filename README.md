# 介護・医療・福祉特化 求人メディア構築パッケージ

顧客(介護業界団体・介護特化の人材会社・地域メディア)に導入すると、複数の介護事業所の求人が掲載される求人メディアが立ち上がるパッケージ。
**顧客ごとに独立した環境(エックスサーバーを1契約)へ設置する。1環境 = 1メディア。**

| ドキュメント | 内容 |
|---|---|
| [SPEC.md](SPEC.md) | 仕様書。機能・データモデル・テーマ機構・判断の背景 |
| [CLAUDE.md](CLAUDE.md) | 実装ルール。絶対ルールと本番環境の制約 |
| [TASKS.md](TASKS.md) | 実行計画。タスクと完了判定条件 |

---

## 技術スタック

| 領域 | 採用 | 備考 |
|---|---|---|
| フレームワーク | Laravel 13.8 | |
| PHP | **8.4** | 8.3 はセキュリティ修正が 2026年12月終了のため採用しない |
| DB | MySQL 8.0 | 1環境 = 1DB。テナント分離の仕組みは持たない |
| CSS | Tailwind CSS 4 | |
| ビルド | Vite 8 | Node 20+ が必要。**本番にはないためローカルでビルドする** |
| 本番 | エックスサーバー(共有レンタル) | Apache + `.htaccess` |

---

## ローカル開発環境の起動

前提: Docker Desktop が起動していること。

```bash
# 1. 環境変数ファイルを用意する(初回のみ)
cp .env.example .env

# 2. コンテナを起動する
docker compose up -d --build

# 3. アプリケーションキーを生成する(初回のみ)
docker compose exec app php artisan key:generate

# 4. 依存をインストールする(初回のみ)
docker compose exec app composer install

# 5. マイグレーションを実行する
docker compose exec app php artisan migrate

# 6. フロントをビルドする
docker compose run --rm node npm install
docker compose run --rm node npm run build
```

### アクセス先

| 用途 | URL |
|---|---|
| アプリケーション | http://localhost:8080 |
| メール受信箱(mailpit) | http://localhost:8025 |
| MySQL(外部ツールから) | `localhost:3307` / user: `kyujin` / pass: `secret` |

---

## よく使うコマンド

```bash
# テスト(MySQL の kyujin_testing に対して実行される)
docker compose exec app php artisan test
docker compose exec app php artisan test tests/Feature/Admin

# artisan
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker

# フロントの開発サーバ
docker compose run --rm --service-ports node npm run dev

# コード整形
docker compose exec app ./vendor/bin/pint

# コンテナの停止 / 破棄
docker compose down
docker compose down -v   # DB のデータも消す
```

---

## 本番環境との差異(重要)

本番は**共有レンタルサーバー**であり、ローカルの Docker とは構成が異なる。
**ローカルで動いても本番で動かない実装をしないこと。**

| ローカル | 本番(エックスサーバー) |
|---|---|
| Docker | 使えない。素の PHP + Apache |
| Node.js コンテナでビルド | **Node.js がない。`public/build` を git 経由で配布する** |
| キューを常時起動できる | **常駐プロセス不可。cron で毎分 `queue:work --stop-when-empty` を回す** |
| ポート 8080 | 80 / 443 |

詳細は [CLAUDE.md 4章](CLAUDE.md) と [SPEC.md 13章](SPEC.md) を参照。

### `public/build` を `.gitignore` に入れてはいけない

本番に Node.js がないためサーバ上でビルドできない。ローカルでビルドした成果物をリポジトリに含めて配布する。
**フロントを変更したら、必ずビルドしてからコミットすること。**

---

## ディレクトリ構成

```
app/Http/Controllers/
  Public/     公開メディアサイト(求職者が見る画面)
  Company/    掲載企業の管理画面
  Admin/      運営者の管理画面
  Seeker/     求職者マイページ
  Feed/       アグリゲーション向けフィード出力
resources/views/
  core/       全顧客共通のテンプレート(フォールバック)
  themes/     顧客ごとのテーマ(見た目のみ。ロジックを書かない)
docker/       ローカル開発用の Docker 設定
docs/         運用手順書(環境構築・デプロイ・導入手順)
```
