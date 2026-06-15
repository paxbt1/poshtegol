FROM php:8.3-fpm-alpine AS base

ARG APK_MIRROR=https://mirrors.aliyun.com/alpine
RUN printf '%s/v%s/main\n%s/v%s/community\n' "$APK_MIRROR" "$(cut -d. -f1,2 /etc/alpine-release)" "$APK_MIRROR" "$(cut -d. -f1,2 /etc/alpine-release)" > /etc/apk/repositories

RUN apk add --no-cache \
    bash curl git icu-dev libzip-dev oniguruma-dev freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
    nginx supervisor nodejs npm sqlite sqlite-dev postgresql-dev mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_sqlite intl zip bcmath gd opcache \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build && rm -rf node_modules \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/database bootstrap/cache public/media/news/images public/media/ads \
    && touch storage/database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache public/media

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
