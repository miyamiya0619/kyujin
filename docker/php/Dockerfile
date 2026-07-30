# 本番(エックスサーバー)は Apache + PHP 8.4 のため、ローカルも同じ構成にする。
# PHP 8.3 はセキュリティ修正が 2026年12月で終了するため採用しない。
# エックスサーバーの新規ドメインの初期値は 8.3 なので、環境構築時に 8.4 へ切り替えること。
#
# 本番にない拡張を入れると「ローカルで動くが本番で動かない」実装を招くので、
# エックスサーバーで利用できる拡張だけを入れること。
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 本番はドキュメントルートが public_html(= Laravel の public へのシンボリックリンク)。
# ローカルでも public をドキュメントルートにして構成を揃える。
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php.ini は docker-compose.yml でマウントする。
# ビルドコンテキストに依存しないため、値を変えても再ビルドが不要になる。

WORKDIR /var/www/html
