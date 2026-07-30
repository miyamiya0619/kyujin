# エックスサーバー 実機調査手順(T-01)

新しい顧客環境を作るたびに、この調査を最初に行う。
**結果は [SPEC.md 13.1](../SPEC.md) の「要確認事項」に反映すること。**
ここで判明する値が、キューの回し方・バッチのチャンクサイズ・画像処理の実装方針を決める。

---

## 1. 事前準備(サーバーパネル)

| 項目 | 操作 |
|---|---|
| PHP バージョン | 「PHP Ver.切替」で **PHP 8.4** に変更する。新規ドメインの初期値は 8.3 なので必ず切り替える |
| SSH | 「SSH設定」で ON にし、公開鍵を登録する |
| cron | 「Cron設定」で毎分実行が設定できるか確認する |

---

## 2. SSH で接続して以下を実行する

```bash
# --- PHP -------------------------------------------------------
php -v
# → 8.4.x であること。8.3 のままなら CLI 版の切り替えが必要
#    (サーバーパネルの設定は Web 版のみに効く場合がある)

which php php8.4 php84
# → CLI で 8.4 を呼ぶパスを確認する。cron の設定で使う

# --- 拡張モジュール ---------------------------------------------
php -m
# → 以下がすべてあることを確認する
#    pdo_mysql / mbstring / gd / zip / bcmath / exif / curl / openssl
#    / tokenizer / xml / fileinfo / ctype / json
# → Imagick があるかも確認する(なければ画像処理は GD で実装する)

# --- PHP の実効値 ----------------------------------------------
php -i | grep -E "^(memory_limit|max_execution_time|post_max_size|upload_max_filesize|max_input_vars|date.timezone)"
# → docker/php/php.ini をこの値に合わせる。
#    ローカルのほうが緩いと「ローカルで動くが本番で落ちる」実装を招く

# --- コマンドの有無 --------------------------------------------
git --version
composer --version || echo "composer なし(composer.phar を配置する)"
mysql --version
mysqldump --version
# → git がなければ rsync でのデプロイに切り替える(T-18 の方針が変わる)

# --- symlink が使えるか(重要) ---------------------------------
cd ~
php -r 'var_dump(function_exists("symlink"));'
mkdir -p _symlink_test/target && ln -s ~/_symlink_test/target ~/_symlink_test/link && ls -l ~/_symlink_test/
rm -rf ~/_symlink_test
# → 使えないと public_html の構成と storage:link が成立しない。
#    その場合は .htaccess でのサブディレクトリ rewrite に切り替える

# --- ディスクとメモリ ------------------------------------------
df -h ~
free -m 2>/dev/null || echo "free 不可(共有サーバのため)"
```

---

## 3. 記録する項目

調査後、この表を埋めて SPEC.md 13.1 に反映する。

| 項目 | 実測値 | 備考 |
|---|---|---|
| PHP バージョン(Web) | | 8.4 であること |
| PHP バージョン(CLI) | | cron で使うパスも記録 |
| GD | | |
| Imagick | | なければ画像処理は GD で実装 |
| memory_limit | | |
| max_execution_time | | バッチのチャンクサイズを決める根拠 |
| post_max_size / upload_max_filesize | | 画像アップロードの上限 |
| git | | なければデプロイ方式を変更 |
| composer | | なければ composer.phar を配置 |
| symlink() | | 使えないと public_html 構成が変わる |
| cron の最小間隔 | | 1分でないとキューの遅延が延びる |

---

## 4. 判明した値で変更するもの

| 判明した内容 | 変更対象 |
|---|---|
| PHP の実効値 | `docker/php/php.ini` を本番に合わせる |
| Imagick がない | 画像処理を GD 前提で実装する |
| cron が1分間隔で回せない | キューの遅延を仕様に反映し、通知の文言を見直す |
| git がない | `deploy.sh` を rsync ベースに変更する(TASKS.md T-18) |
| symlink() が使えない | [SPEC.md 13.2](../SPEC.md) の環境構成を見直す |
