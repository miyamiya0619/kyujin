-- テスト用データベース。php artisan test が本番データを壊さないようにする。
CREATE DATABASE IF NOT EXISTS `kyujin_testing`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `kyujin_testing`.* TO 'kyujin'@'%';
FLUSH PRIVILEGES;
