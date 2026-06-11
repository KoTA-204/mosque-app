# ---- Stage 1: build aset frontend (Vite) ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.* ./
RUN npm ci
COPY resources resources
COPY public public
RUN npm run build

# ---- Stage 2: image aplikasi produksi ----
FROM serversideup/php:8.3-fpm-nginx AS app
WORKDIR /var/www/html

USER root
RUN install-php-extensions gd
USER www-data

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# source code + aset hasil build
COPY --chown=www-data:www-data . .
COPY --from=assets /app/public/build public/build

RUN rm -f public/hot
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
# install dependency (HARUS sukses — jangan ditelan || true)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

RUN php artisan package:discover --ansi
# jalankan script artisan setelah autoload siap (ini boleh gagal tanpa menggagalkan build)
RUN php artisan storage:link || true

# serversideup image listen di port 8080
ENV PHP_OPCACHE_ENABLE=1